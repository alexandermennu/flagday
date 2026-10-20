<?php

namespace Tests\Feature\Admin;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProgramTypesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    private function payload(string $type, array $overrides = []): array
    {
        return array_merge(Program::factory()->raw(), ['type' => $type], $overrides);
    }

    private function pageCount(string $pdf): int
    {
        return preg_match_all('#/Type\s*/Page[^s]#', $pdf);
    }

    private function fakePdf(string $marker = 'convocation'): UploadedFile
    {
        $content = "%PDF-1.4\n% {$marker}\n1 0 obj << /Type /Catalog >> endobj\ntrailer << /Root 1 0 R >>\n%%EOF\n";

        return UploadedFile::fake()->createWithContent('convocation-program.pdf', $content);
    }

    // ---------------------------------------------------------------- form

    public function test_form_has_a_required_program_type_selector_and_loads_the_saved_type(): void
    {
        $this->get(route('admin.programs.create'))
            ->assertOk()
            ->assertSee('Program Type')
            ->assertSee('<option value="honors-ball" selected', escape: false);

        $program = Program::factory()->create(['type' => 'convocation']);

        $this->get(route('admin.programs.edit', $program))
            ->assertOk()
            ->assertSee('<option value="convocation" selected', escape: false)
            ->assertSee('Convocation program PDF');

        $this->post(route('admin.programs.store'), $this->payload('honors-ball', ['type' => '']))
            ->assertSessionHasErrors('type');
        $this->post(route('admin.programs.store'), $this->payload('prom'))
            ->assertSessionHasErrors('type');
    }

    public function test_existing_programs_are_treated_as_honors_ball(): void
    {
        // Programs created before the type column existed get its default.
        $id = DB::table('programs')->insertGetId(collect(Program::factory()->raw())
            ->except('type')
            ->map(fn ($value) => is_array($value) ? json_encode($value) : $value)
            ->all() + ['created_at' => now(), 'updated_at' => now()]);

        $this->assertSame('honors-ball', Program::find($id)->type);
    }

    public function test_new_baccalaureate_and_convocation_programs_do_not_start_with_honors_content(): void
    {
        $this->get(route('admin.programs.create', ['type' => 'baccalaureate']))
            ->assertOk()
            ->assertSee('<option value="baccalaureate" selected', escape: false)
            ->assertDontSee('value="Presentation of Awards"', escape: false)
            ->assertDontSee('value="Starz Legend Award"', escape: false);
    }

    // ---------------------------------------------------------------- honors ball

    public function test_honors_ball_still_generates_four_pages_and_publishes_to_the_honors_ball_page(): void
    {
        $this->post(route('admin.programs.store'), $this->payload('honors-ball', ['action' => 'generate']));
        $program = Program::sole();

        $this->assertSame(4, $program->pdf_page_count);
        $this->assertSame(4, $this->pageCount($program->pdf->bytes()));

        $this->post(route('admin.programs.publish', $program))->assertSessionHasNoErrors();

        $this->get(route('events.show', 'honors-ball'))
            ->assertSee(route('events.program', 'honors-ball'))
            ->assertSee('RSVP for the Honors Ball');
        $this->get(route('events.show', 'baccalaureate'))->assertDontSee(route('events.program', 'baccalaureate'));

        $response = $this->get(route('events.program', 'honors-ball'));
        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertSame(4, $this->pageCount($response->getContent()));
    }

    // ---------------------------------------------------------------- baccalaureate

    public function test_baccalaureate_generates_only_the_agenda_page(): void
    {
        $this->post(route('admin.programs.store'), $this->payload('baccalaureate', [
            'title' => 'Baccalaureate Service',
            'agenda' => [
                ['style' => 'item', 'title' => 'Processional'],
                ['style' => 'feature', 'title' => 'Baccalaureate Address'],
                ['style' => 'item', 'title' => 'Recessional'],
            ],
            'action' => 'generate',
        ]))->assertSessionHasNoErrors();

        $program = Program::sole();
        $this->assertSame('baccalaureate', $program->type);
        $this->assertSame(1, $program->pdf_page_count);
        $this->assertSame(1, $this->pageCount($program->pdf->bytes()));

        $preview = $this->get(route('admin.programs.preview', $program));
        $preview->assertOk();
        $this->assertSame(1, $this->pageCount($preview->getContent()));
    }

    public function test_baccalaureate_ignores_honors_only_sections_so_they_cannot_block_a_save(): void
    {
        $this->post(route('admin.programs.store'), $this->payload('baccalaureate', [
            // Hidden for Baccalaureate — invalid values here must not block saving.
            'awards' => [['name' => '', 'description' => str_repeat('x', 5000)]],
            'honors_line1' => str_repeat('W', 200),
        ]))->assertSessionHasNoErrors();

        $this->assertSame('baccalaureate', Program::sole()->type);
    }

    public function test_baccalaureate_publishes_to_the_baccalaureate_page(): void
    {
        $program = Program::factory()->create(['type' => 'baccalaureate']);

        $this->post(route('admin.programs.publish', $program))->assertSessionHasNoErrors();

        $this->get(route('events.show', 'baccalaureate'))
            ->assertSee(route('events.program', 'baccalaureate'))
            ->assertSee('Download PDF');
        $this->get(route('class.show'))->assertDontSee(route('events.program', 'baccalaureate')); // booklets list removed from this page

        $this->assertSame(1, $this->pageCount($this->get(route('events.program', 'baccalaureate'))->getContent()));
        $this->get(route('events.program', 'honors-ball'))->assertNotFound();
    }

    // ---------------------------------------------------------------- convocation

    public function test_convocation_saves_an_uploaded_pdf_without_generating_anything(): void
    {
        $this->post(route('admin.programs.store'), $this->payload('convocation', [
            'title' => 'Convocation',
            'program_pdf' => $this->fakePdf('upload-one'),
            'action' => 'generate', // ignored for an uploaded program
        ]))->assertSessionHasNoErrors();

        $program = Program::sole();
        $this->assertSame('convocation', $program->type);
        $this->assertTrue($program->hasPdf());
        $this->assertNull($program->pdf_page_count);
        $this->assertStringContainsString('upload-one', $program->pdf->bytes());

        $this->post(route('admin.programs.generate', $program))->assertSessionHasErrors('pdf');
        $this->get(route('admin.programs.preview', $program))->assertSessionHasErrors('pdf');

        $download = $this->get(route('admin.programs.download', $program));
        $download->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('upload-one', $download->getContent());

        $this->get(route('admin.programs.edit', $program))
            ->assertSee('Uploaded PDF')
            ->assertDontSee('Regenerate PDF');
    }

    public function test_convocation_keeps_saved_agenda_content_it_does_not_use(): void
    {
        $program = Program::factory()->create(['type' => 'convocation']);
        $agenda = $program->agenda;

        $this->put(route('admin.programs.update', $program), $this->payload('convocation', [
            'agenda' => [['style' => 'item', 'title' => '']],
            'awards' => [],
        ]))->assertSessionHasNoErrors();

        $this->assertSame($agenda, $program->fresh()->agenda);
    }

    public function test_convocation_publishes_the_uploaded_pdf_for_online_viewing_and_download(): void
    {
        $this->post(route('admin.programs.store'), $this->payload('convocation', ['program_pdf' => $this->fakePdf('published-file')]));
        $program = Program::sole();

        $this->post(route('admin.programs.publish', $program))->assertSessionHasNoErrors();

        $this->get(route('events.show', 'convocation'))
            ->assertSee(route('events.program', 'convocation'))
            ->assertSee(route('events.program', ['convocation', 'download' => 1]));

        $view = $this->get(route('events.program', 'convocation'));
        $view->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('inline;', $view->headers->get('Content-Disposition'));
        $this->assertStringContainsString('no-cache', $view->headers->get('Cache-Control'));
        $this->assertStringContainsString('published-file', $view->getContent());

        $download = $this->get(route('events.program', ['convocation', 'download' => 1]));
        $this->assertStringStartsWith('attachment;', $download->headers->get('Content-Disposition'));
    }

    public function test_the_public_page_keeps_the_published_copy_until_the_next_publish(): void
    {
        $this->post(route('admin.programs.store'), $this->payload('convocation', ['program_pdf' => $this->fakePdf('first-version')]));
        $program = Program::sole();
        $this->post(route('admin.programs.publish', $program));

        // Replacing the file updates Download PDF but not the public page…
        $this->put(route('admin.programs.update', $program), $this->payload('convocation', ['program_pdf' => $this->fakePdf('second-version')]));
        $this->assertStringContainsString('second-version', $this->get(route('admin.programs.download', $program))->getContent());
        $this->assertStringContainsString('first-version', $this->get(route('events.program', 'convocation'))->getContent());

        // …until it is published again.
        $this->post(route('admin.programs.publish', $program));
        $this->assertStringContainsString('second-version', $this->get(route('events.program', 'convocation'))->getContent());
        $this->assertSame(2, $program->fresh()->published_version);
    }

    public function test_convocation_cannot_be_published_without_an_uploaded_pdf(): void
    {
        $program = Program::factory()->create(['type' => 'convocation']);

        $this->post(route('admin.programs.publish', $program))->assertSessionHasErrors('pdf');
        $this->assertNull($program->fresh()->published_at);
    }

    public function test_uploads_must_be_real_pdfs_within_the_configured_size(): void
    {
        $this->post(route('admin.programs.store'), $this->payload('convocation', [
            'program_pdf' => UploadedFile::fake()->image('program.png'),
        ]))->assertSessionHasErrors('program_pdf');

        // Right extension and MIME type, but not actually a PDF.
        $this->post(route('admin.programs.store'), $this->payload('convocation', [
            'program_pdf' => UploadedFile::fake()->createWithContent('program.pdf', 'not a pdf at all'),
        ]))->assertSessionHasErrors('program_pdf');

        config(['graduation.program_pdf_max_kb' => 1]);
        $this->post(route('admin.programs.store'), $this->payload('convocation', [
            'program_pdf' => UploadedFile::fake()->createWithContent('program.pdf', '%PDF-1.4'.str_repeat(' ', 4096)),
        ]))->assertSessionHasErrors('program_pdf');

        $this->assertDatabaseCount('programs', 0);
    }

    // ---------------------------------------------------------------- switching types

    public function test_changing_the_type_removes_the_old_pdf_and_unpublishes(): void
    {
        $program = Program::factory()->create();
        $this->post(route('admin.programs.publish', $program));
        $this->assertNotNull(Program::publishedFor('honors-ball'));

        $this->put(route('admin.programs.update', $program), $this->payload('convocation'))->assertSessionHasNoErrors();

        $program->refresh();
        $this->assertSame('convocation', $program->type);
        $this->assertFalse($program->hasPdf());
        $this->assertNull($program->published_at);
        $this->get(route('events.program', 'honors-ball'))->assertNotFound();
        $this->get(route('events.program', 'convocation'))->assertNotFound();
    }
}
