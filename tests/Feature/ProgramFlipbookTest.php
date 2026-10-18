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

        $this->get(route('class.show'))->assertSee(route('events.program.viewer', 'honors-ball'));

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

    public function test_events_without_the_flipbook_enabled_keep_the_plain_pdf_link(): void
    {
        $this->publish('baccalaureate');

        $this->get(route('events.program.viewer', 'baccalaureate'))->assertNotFound();
        $this->get(route('events.show', 'baccalaureate'))
            ->assertSee('View Program (PDF)')
            ->assertDontSee(route('events.program.viewer', 'baccalaureate'));
    }

    public function test_the_viewer_can_be_enabled_for_another_event_by_config(): void
    {
        config(['graduation.events.baccalaureate.flipbook' => true]);
        $this->publish('baccalaureate');

        $this->get(route('events.show', 'baccalaureate'))->assertSee(route('events.program.viewer', 'baccalaureate'));
        $this->get(route('events.program.viewer', 'baccalaureate'))
            ->assertOk()
            ->assertSee('Baccalaureate Program')
            ->assertSee('data-pdf-url="'.route('events.program', 'baccalaureate').'"', escape: false);
    }
}
