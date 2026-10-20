<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramFlipbookTest extends TestCase
{
    use RefreshDatabase;

    private function publish(string $type): Program
    {
        $this->actingAs(User::factory()->create());
        $program = Program::factory()->create(['type' => $type]);
        $this->post(route('admin.programs.publish', $program))->assertSessionHasNoErrors();
        auth()->logout();

        return $program;
    }

    public function test_viewer_is_unavailable_until_the_honors_ball_program_is_published(): void
    {
        $this->get(route('events.program.viewer', 'honors-ball'))->assertNotFound();
    }

    public function test_honors_ball_program_opens_in_the_flipbook_viewer(): void
    {
        $this->publish('honors-ball');

        $this->get(route('events.show', 'honors-ball'))
            ->assertSee(route('events.program.viewer', 'honors-ball'))
            ->assertSee(route('events.program', ['honors-ball', 'download' => 1]))
            ->assertSee('RSVP for the Honors Ball');

        // The Class of 2026 page no longer lists program booklets; the event page is the way in.
        $this->get(route('class.show'))->assertDontSee(route('events.program.viewer', 'honors-ball'));

        $this->get(route('events.program.viewer', 'honors-ball'))
            ->assertOk()
            ->assertSee('Honors Ball Program')
            // The viewer renders the same published PDF the event page offers…
            ->assertSee('data-pdf-url="'.route('events.program', 'honors-ball').'"', escape: false)
            // …keeps a download of the original…
            ->assertSee(route('events.program', ['honors-ball', 'download' => 1]))
            // …loads the viewer script, and has prev/next controls.
            ->assertSee('flipbook-', escape: false)
            ->assertSee('data-flipbook-prev', escape: false)
            ->assertSee('data-flipbook-next', escape: false);

        // The PDF it loads is the real, four-page generated program.
        $pdf = $this->get(route('events.program', 'honors-ball'))->assertOk()->getContent();
        $this->assertSame(4, preg_match_all('#/Type\s*/Page[^s]#', $pdf));
    }

    public function test_honors_ball_shows_its_program_card_once_in_the_hero(): void
    {
        $this->publish('honors-ball');

        $html = $this->get(route('events.show', 'honors-ball'))->assertOk()->getContent();
        $heroEnd = strpos($html, 'Event Details</h2>');

        $this->assertSame(1, substr_count($html, 'data-program-card'), 'The card appears exactly once.');
        $this->assertLessThan($heroEnd, strpos($html, 'data-program-card'), 'The card sits in the hero, above Event Details.');
        $this->assertStringContainsString(route('events.program.viewer', 'honors-ball'), substr($html, 0, $heroEnd));
        $this->assertStringContainsString(route('events.program', ['honors-ball', 'download' => 1]), substr($html, 0, $heroEnd));
        $this->assertStringContainsString('Go to the RSVP form', substr($html, $heroEnd), 'The RSVP card stays in the details section.');
    }

    public function test_other_events_keep_the_program_card_in_the_details_section(): void
    {
        $html = $this->get(route('events.show', 'baccalaureate'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'data-program-card'));
        $this->assertGreaterThan(strpos($html, 'Event Details</h2>'), strpos($html, 'data-program-card'));
    }

    public function test_baccalaureate_program_opens_in_the_same_viewer_with_its_own_pdf(): void
    {
        $this->publish('honors-ball');
        $this->publish('baccalaureate');

        // The card's view button goes to the branded viewer, not the raw PDF.
        $this->get(route('events.show', 'baccalaureate'))
            ->assertSee(route('events.program.viewer', 'baccalaureate'))
            ->assertDontSee('View Program (PDF)')
            ->assertSee(route('events.program', ['baccalaureate', 'download' => 1]));

        // The viewer is handed the Baccalaureate PDF — never the Honors Ball one.
        $this->get(route('events.program.viewer', 'baccalaureate'))
            ->assertOk()
            ->assertSee('Baccalaureate Program')
            ->assertSee('data-pdf-url="'.route('events.program', 'baccalaureate').'"', escape: false)
            ->assertDontSee(route('events.program', 'honors-ball'))
            ->assertSee(route('events.program', ['baccalaureate', 'download' => 1]));

        // That URL serves the one-page Baccalaureate program (the Honors Ball one has four pages).
        $pdf = $this->get(route('events.program', 'baccalaureate'))->assertOk()->assertHeader('Content-Type', 'application/pdf')->getContent();
        $this->assertSame(1, preg_match_all('#/Type\s*/Page[^s]#', $pdf));
        $this->assertStringStartsWith('attachment;', $this->get(route('events.program', ['baccalaureate', 'download' => 1]))->headers->get('Content-Disposition'));

        // Honors Ball is unchanged.
        $this->get(route('events.program.viewer', 'honors-ball'))
            ->assertOk()
            ->assertSee('data-pdf-url="'.route('events.program', 'honors-ball').'"', escape: false);
    }

    public function test_events_without_the_flipbook_enabled_keep_the_plain_pdf_link(): void
    {
        $this->publishConvocation();

        $this->get(route('events.program.viewer', 'convocation'))->assertNotFound();
        $this->get(route('events.show', 'convocation'))
            ->assertSee('View Program (PDF)')
            ->assertDontSee(route('events.program.viewer', 'convocation'));
    }

    public function test_the_viewer_can_be_enabled_for_another_event_by_config(): void
    {
        config(['graduation.events.convocation.flipbook' => true]);
        $this->publishConvocation();

        $this->get(route('events.show', 'convocation'))->assertSee(route('events.program.viewer', 'convocation'));
        $this->get(route('events.program.viewer', 'convocation'))
            ->assertOk()
            ->assertSee('Convocation Program')
            ->assertSee('data-pdf-url="'.route('events.program', 'convocation').'"', escape: false);
    }

    /** Convocation programs are uploaded rather than generated, so give it a PDF before publishing. */
    private function publishConvocation(): Program
    {
        $this->actingAs(User::factory()->create());
        $program = Program::factory()->create(['type' => 'convocation']);
        $program->pdf()->create(['data' => base64_encode("%PDF-1.4\n%%EOF\n")]);
        $program->forceFill(['pdf_generated_at' => now(), 'pdf_size' => 15])->save();
        $this->post(route('admin.programs.publish', $program))->assertSessionHasNoErrors();
        auth()->logout();

        return $program;
    }
}
