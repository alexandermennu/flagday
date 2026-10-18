<?php

namespace Tests\Feature\Admin;

use App\Models\Program;
use App\Models\User;
use App\Services\ProgramPdfRenderer;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProgramTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    private function payload(array $overrides = []): array
    {
        return array_merge(Program::factory()->raw(), [
            'agenda' => [
                ['style' => 'item', 'title' => 'Invocation'],
                ['style' => 'feature', 'title' => 'Presentation of Awards'],
                ['style' => 'item', 'title' => 'Vote of thanks'],
            ],
            'awards' => [
                ['name' => 'Leadership Award', 'description' => 'For outstanding leadership.'],
                ['name' => 'Legend Award', 'description' => 'The highest recognition.'],
            ],
        ], $overrides);
    }

    private function pageCount(string $pdf): int
    {
        return preg_match_all('#/Type\s*/Page[^s]#', $pdf);
    }

    public function test_program_pages_require_login(): void
    {
        auth()->logout();

        $this->get(route('admin.programs.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.programs.create'))->assertRedirect(route('admin.login'));
    }

    public function test_create_form_starts_from_the_honors_awards_template(): void
    {
        $this->get(route('admin.programs.create'))
            ->assertOk()
            ->assertSee('Presentation of Awards')
            ->assertSee('Starz Legend Award')
            ->assertSee('Awards Categories &amp; Description', escape: false);
    }

    public function test_saving_a_program_stores_its_details_lists_and_images(): void
    {
        $response = $this->post(route('admin.programs.store'), $this->payload([
            'images' => ['seal_left' => UploadedFile::fake()->image('seal.png', 400, 400)],
        ]));

        $program = Program::sole();
        $response->assertRedirect(route('admin.programs.edit', $program));

        $this->assertSame('Special Honors Awards', $program->title);
        $this->assertSame('2026-11-21', $program->event_date->toDateString());
        $this->assertSame(['Invocation', 'Presentation of Awards', 'Vote of thanks'], array_column($program->agenda, 'title'));
        $this->assertSame('feature', $program->agenda[1]['style']);
        $this->assertSame(['Leadership Award', 'Legend Award'], array_column($program->awards, 'name'));

        $seal = $program->asset('seal_left');
        $this->assertNotNull($seal);
        $this->assertSame('image/png', $seal->mime);
        $this->assertFalse($program->hasPdf(), 'Saving alone must not generate a PDF.');
    }

    public function test_editing_reorders_agenda_removes_awards_and_marks_the_pdf_out_of_date(): void
    {
        $program = Program::factory()->create($this->payload());
        $this->post(route('admin.programs.generate', $program))->assertSessionHasNoErrors();
        $this->travel(5)->seconds();

        $this->put(route('admin.programs.update', $program), $this->payload([
            'venue_name' => 'Updated Hall',
            'agenda' => [
                ['style' => 'item', 'title' => 'Vote of thanks'],
                ['style' => 'item', 'title' => 'Invocation'],
                ['style' => 'item', 'title' => ''], // blank row added and left empty — ignored
            ],
            'awards' => [['name' => 'Legend Award', 'description' => 'The highest recognition.']],
        ]))->assertRedirect(route('admin.programs.edit', $program));

        $program->refresh();
        $this->assertSame('Updated Hall', $program->venue_name);
        $this->assertSame(['Vote of thanks', 'Invocation'], array_column($program->agenda, 'title'));
        $this->assertSame(['Legend Award'], array_column($program->awards, 'name'));
        $this->assertTrue($program->pdfIsStale());

        $this->get(route('admin.programs.edit', $program))->assertSee('edited after this PDF was generated');
    }

    public function test_generating_stores_a_four_page_pdf_that_can_be_downloaded(): void
    {
        $program = Program::factory()->create();

        $this->get(route('admin.programs.download', $program))
            ->assertRedirect()
            ->assertSessionHasErrors('pdf');

        $this->post(route('admin.programs.generate', $program))->assertSessionHasNoErrors();

        $program->refresh();
        $this->assertTrue($program->hasPdf());
        $this->assertSame(4, $program->pdf_page_count);
        $this->assertFalse($program->pdfIsStale());

        $response = $this->get(route('admin.programs.download', $program));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'attachment; filename="starz-university-special-honors-awards-2026-program.pdf"');

        $pdf = $response->getContent();
        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertSame(4, $this->pageCount($pdf));
    }

    public function test_save_and_generate_in_one_step(): void
    {
        $this->post(route('admin.programs.store'), $this->payload(['action' => 'generate']))
            ->assertSessionHas('success', 'Program created. PDF generated.');

        $this->assertTrue(Program::sole()->hasPdf());
    }

    public function test_publishing_bumps_the_version_and_regenerates_the_pdf(): void
    {
        $program = Program::factory()->create();

        $this->post(route('admin.programs.publish', $program))->assertSessionHasNoErrors();
        $program->refresh();
        $this->assertSame(1, $program->published_version);
        $this->assertNotNull($program->published_at);
        $firstGeneratedAt = $program->pdf_generated_at;

        $this->travel(5)->seconds();
        $program->update(['venue_name' => 'New Venue']);
        $this->post(route('admin.programs.publish', $program))->assertSessionHasNoErrors();

        $program->refresh();
        $this->assertSame(2, $program->published_version);
        $this->assertTrue($program->pdf_generated_at->greaterThan($firstGeneratedAt));
        $this->assertFalse($program->pdfIsStale());
    }

    public function test_preview_renders_the_saved_content_inline_without_storing_it(): void
    {
        $program = Program::factory()->create();

        $response = $this->get(route('admin.programs.preview', $program));

        $response->assertOk();
        $this->assertStringStartsWith('inline;', $response->headers->get('Content-Disposition'));
        $this->assertSame(4, $this->pageCount($response->getContent()));
        $this->assertFalse($program->fresh()->hasPdf());
    }

    public function test_maximum_length_content_still_fits_four_pages_with_no_text_outside_the_page(): void
    {
        $long = fn (int $length) => mb_substr(str_repeat('Extraordinary dedication to excellence and service. ', 60), 0, $length);
        $unbroken = str_repeat('W', 40); // a single very long word must not run past its column

        $program = Program::factory()->create([
            'title' => $long(80),
            'class_year' => '2026',
            'edition_label' => $long(60),
            'venue_name' => $long(80),
            'venue_address' => $long(120),
            'description' => $long(850).' '.$unbroken,
            'hosted_by' => $long(80),
            'institution_name' => $long(40),
            'institution_address_line1' => $long(60),
            'institution_address_line2' => $long(60),
            'contact_phone' => $long(60),
            'contact_email' => 'a-very-long-mailbox-name@an-extremely-long-domain-example.org',
            'contact_website' => $long(80),
            'mark_top' => $long(14),
            'mark_script' => $long(14),
            'mark_bottom' => $long(14),
            'honors_line1' => str_repeat('W', 24),
            'honors_ribbon' => str_repeat('W', 30),
            'honors_line2' => str_repeat('W', 24),
            'honors_line3' => str_repeat('W', 24),
            'awards_heading' => $long(80),
            'agenda' => array_map(fn ($i) => ['style' => $i % 10 === 0 ? 'feature' : 'item', 'title' => $long(76).' '.$i], range(1, 26)),
            'awards' => array_map(fn ($i) => ['name' => $long(70).' '.$i, 'description' => $long(450).' '.$unbroken], range(1, 12)), // 5,892 chars: just under the 6,000 limit
        ]);

        $this->post(route('admin.programs.generate', $program))->assertSessionHasNoErrors();
        $this->assertSame(4, $this->pageCount($program->fresh()->pdf->bytes()));

        $this->assertAllTextInsidePages(app(ProgramPdfRenderer::class), $program->fresh());
    }

    public function test_content_too_long_for_one_page_is_rejected_instead_of_overflowing(): void
    {
        // Bypasses form validation to simulate content far beyond what the page can hold.
        $program = Program::factory()->create([
            'awards' => array_map(fn ($i) => ['name' => "Award $i", 'description' => str_repeat('Lorem ipsum dolor sit amet. ', 60)], range(1, 12)),
        ]);

        $this->from(route('admin.programs.edit', $program))
            ->post(route('admin.programs.generate', $program))
            ->assertRedirect(route('admin.programs.edit', $program))
            ->assertSessionHasErrors(['pdf' => 'The award categories page has more text than fits on one page, even at the smallest text size. Shorten its content and generate the PDF again.']);

        $this->assertFalse($program->fresh()->hasPdf());
    }

    public function test_award_text_over_the_combined_limit_fails_validation(): void
    {
        $this->post(route('admin.programs.store'), $this->payload([
            'awards' => array_map(fn ($i) => ['name' => "Award $i", 'description' => str_repeat('x', 1500)], range(1, 5)),
        ]))->assertSessionHasErrors('awards');
    }

    public function test_images_can_be_replaced_and_removed(): void
    {
        $program = Program::factory()->create();
        $this->put(route('admin.programs.update', $program), $this->payload([
            'images' => ['corner_badge' => UploadedFile::fake()->image('badge.jpg', 3000, 2000)],
        ]));

        $badge = $program->fresh()->asset('corner_badge');
        $this->assertSame('image/jpeg', $badge->mime);
        $this->assertSame(1200, $badge->width, 'Large uploads are scaled down to 1200px on the long side.');

        $this->put(route('admin.programs.update', $program), $this->payload(['remove_images' => ['corner_badge']]));
        $this->assertNull($program->fresh()->asset('corner_badge'));
    }

    public function test_non_image_uploads_are_rejected(): void
    {
        $this->post(route('admin.programs.store'), $this->payload([
            'images' => ['seal_left' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')],
        ]))->assertSessionHasErrors('images.seal_left');
    }

    public function test_deleting_a_program_removes_its_pdf_and_images(): void
    {
        $program = Program::factory()->create();
        $this->post(route('admin.programs.generate', $program));

        $this->delete(route('admin.programs.destroy', $program))->assertRedirect(route('admin.programs.index'));

        $this->assertDatabaseCount('programs', 0);
        $this->assertDatabaseCount('program_pdfs', 0);
    }

    /**
     * Lays out the final document with dompdf and checks every text fragment's box lies
     * within the A4 landscape page — catching text that would be clipped at an edge or
     * run off a column, not just text that spills onto an extra page.
     */
    private function assertAllTextInsidePages(ProgramPdfRenderer $renderer, Program $program): void
    {
        $fit = $renderer->fit($program);

        $options = new Options(app('dompdf.options'));
        $options->setChroot(base_path());
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($renderer->html($program, $fit, probe: true));
        $dompdf->setPaper('a4', 'landscape');

        // dompdf discards each page's frames once drawn, so inspect them as they render.
        [$pageWidth, $pageHeight] = [841.89, 595.28];
        $checked = 0;
        $dompdf->setCallbacks([['event' => 'end_frame', 'f' => function ($frame) use ($pageWidth, $pageHeight, &$checked) {
            if ($frame->get_node()->nodeName !== '#text' || trim($frame->get_text()) === '') {
                return;
            }

            [$x, $y, $width, $height] = $frame->get_border_box();
            $style = $frame->get_style();
            $text = mb_substr(trim($frame->get_text()), 0, 40)." ({$style->font_family} {$style->font_size}pt)";

            $this->assertGreaterThanOrEqual(-0.5, $x, "Text starts left of the page: “{$text}”");
            $this->assertLessThanOrEqual($pageWidth + 0.5, $x + $width, "Text runs past the right edge: “{$text}”");
            $this->assertLessThanOrEqual($pageHeight + 0.5, $y + $height, "Text runs past the bottom edge: “{$text}”");
            $checked++;
        }]]);

        $dompdf->render();

        $this->assertSame(4, $dompdf->getCanvas()->get_page_count());
        $this->assertGreaterThan(100, $checked);
    }
}
