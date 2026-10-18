<?php

namespace Tests\Feature;

use Tests\TestCase;

class GraduationSiteTest extends TestCase
{
    public function test_landing_page_shows_all_three_events_and_honors_ball_rsvp(): void
    {
        $response = $this->get(route('landing'));

        $response->assertOk();
        $response->assertSee('Starz University');
        $response->assertSee('Class of 2026');
        $response->assertSee('Honors Ball');
        $response->assertSee('Baccalaureate');
        $response->assertSee('Convocation');
        $response->assertSee(route('rsvp.create'));
        $response->assertDontSee('Flag Day');
    }

    public function test_unset_event_details_render_as_placeholders(): void
    {
        config(['graduation.events.convocation.date' => null, 'graduation.events.convocation.venue' => null]);

        $this->get(route('events.show', 'convocation'))
            ->assertOk()
            ->assertSee('To be announced')
            ->assertSee('Program coming soon');
    }

    public function test_configured_event_details_are_shown(): void
    {
        config([
            'graduation.events.baccalaureate.date' => '2026-12-06',
            'graduation.events.baccalaureate.time' => '10:00',
            'graduation.events.baccalaureate.venue' => 'Main Auditorium',
            'graduation.events.baccalaureate.program_pdf' => 'programs/baccalaureate.pdf',
        ]);

        $this->get(route('events.show', 'baccalaureate'))
            ->assertOk()
            ->assertSee('Sunday, December 6, 2026')
            ->assertSee('10:00 AM')
            ->assertSee('Main Auditorium')
            ->assertSee(asset('programs/baccalaureate.pdf'));
    }

    public function test_only_the_honors_ball_page_offers_rsvp(): void
    {
        $this->get(route('events.show', 'honors-ball'))->assertSee('RSVP for the Honors Ball');
        $this->get(route('events.show', 'convocation'))->assertDontSee('RSVP for the Convocation');
        $this->get(route('events.show', 'baccalaureate'))->assertDontSee('RSVP for the Baccalaureate');
    }

    public function test_unknown_event_returns_404(): void
    {
        $this->get('/events/prom')->assertNotFound();
    }

    public function test_old_flag_day_url_redirects_to_landing(): void
    {
        $this->get('/flag-day')->assertRedirect('/');
    }
}
