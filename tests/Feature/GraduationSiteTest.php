<?php

namespace Tests\Feature;

use Tests\TestCase;

class GraduationSiteTest extends TestCase
{
    public function test_landing_page_is_hero_only_with_event_summary_and_honors_ball_rsvp(): void
    {
        $response = $this->get(route('landing'));

        $response->assertOk();
        $response->assertSee('Starz University');
        $response->assertSee('Class of 2026');
        $response->assertSee(route('rsvp.create'));
        $response->assertSee(route('class.show'));
        $response->assertSee(route('class.show').'#baccalaureate');
        $response->assertSee(route('class.show').'#convocation');
        $response->assertDontSee('Flag Day');

        // The sections below the hero live on the Class of 2026 page now, and the
        // homepage ends at the hero (no footer).
        $response->assertDontSee('Your Guide to Graduation');
        $response->assertDontSee('Join us at the Honors Ball');
        $response->assertDontSee('<footer', escape: false);
    }

    public function test_navigation_has_exactly_home_class_of_2026_and_honors_ball(): void
    {
        $html = $this->get(route('landing'))->getContent();

        preg_match('/<nav[^>]*aria-label="Primary"[^>]*>(.*?)<\/nav>/s', $html, $nav);
        preg_match_all('/<a [^>]*href="([^"]+)"[^>]*>\s*(.*?)\s*<\/a>/s', $nav[1], $links, PREG_SET_ORDER);

        $this->assertSame([
            ['Home', route('landing')],
            ['Class of 2026', route('class.show')],
            ['Honors Ball', route('events.show', 'honors-ball')],
        ], array_map(fn ($link) => [$link[2], $link[1]], $links));
    }

    public function test_class_of_2026_page_lists_events_and_program_booklets(): void
    {
        config(['graduation.events.convocation.program_pdf' => 'programs/convocation.pdf']);

        $this->get(route('class.show'))
            ->assertOk()
            ->assertSee('Baccalaureate')
            ->assertSee('Convocation')
            ->assertSee('Honors Ball')
            ->assertSee('Digital Program Booklets')
            ->assertSee(asset('programs/convocation.pdf'))
            ->assertSee('Program coming soon')
            ->assertSee('id="baccalaureate"', escape: false)
            ->assertSee('<footer', escape: false);
    }

    public function test_honors_ball_page_has_rsvp_entry_point_and_program_slot(): void
    {
        $this->get(route('events.show', 'honors-ball'))
            ->assertOk()
            ->assertSee('RSVP for the Honors Ball')
            ->assertSee(route('rsvp.create'))
            ->assertSee('Event Program');
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
