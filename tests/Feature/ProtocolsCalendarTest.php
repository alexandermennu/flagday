<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProtocolsCalendarTest extends TestCase
{
    public function test_homepage_hero_buttons_lead_to_program_sheets_and_protocols(): void
    {
        $html = $this->get(route('landing'))->assertOk()->getContent();
        $hero = substr($html, strpos($html, '<main'), strpos($html, '</main>') - strpos($html, '<main'));

        $this->assertStringContainsString('View Program Sheets', $hero);
        $this->assertStringContainsString('Protocols &amp; Calendar', $hero);
        $this->assertStringContainsString('href="'.route('class.show').'#events"', $hero);
        $this->assertStringContainsString('href="'.route('protocols.show').'"', $hero);
        $this->assertStringNotContainsString('RSVP for the Honors Ball', $hero);
        $this->assertStringNotContainsString('View Graduation Events', $hero);
    }

    public function test_program_sheets_destination_keeps_the_three_event_cards(): void
    {
        $html = $this->get(route('class.show'))->assertOk()->getContent();

        $this->assertStringContainsString('id="events"', $html);
        foreach (['honors-ball', 'baccalaureate', 'convocation'] as $slug) {
            $this->assertStringContainsString('href="'.route('events.show', $slug).'"', $html);
        }
    }

    public function test_protocols_page_lists_the_protocols_in_order(): void
    {
        $response = $this->get(route('protocols.show'))->assertOk();

        $response->assertSeeInOrder([
            'Protocols',
            'From 8:00 a.m.', 'The students arrive at the Monrovia Christian Fellowship premise',
            'From 8:30 a.m.', 'The University Faculty arrive',
            'From 9:00 a.m.', 'Other Guests arrive',
            'At 9:30 a.m.', 'The Board of Governors and special Guests arrive',
            'At 10:15 a.m.', 'The Guest Speaker will arrive',
            'At 10:30 a.m.', 'The Academic Procession will commence.',
        ]);
    }

    public function test_calendar_shows_each_event_with_its_details(): void
    {
        $response = $this->get(route('protocols.show'))->assertOk();

        $response->assertSeeInOrder([
            'Calendar of Events',
            'Rooted in Excellence, Rising to Global Challenges',
            'Friday, November 14, 2025', 'Commencement Rehearsal', 'Attendance Mandatory', '3:30 p.m.', 'New Campus, Barrolle Practice Ground Road',
            'Saturday, November 22, 2025', 'Awards Night', '7:30 p.m. Prompt', 'Shatim Event Center, Duport Road',
            'Sunday, November 23, 2025', 'Baccalaureate Service', '2:00 PM', 'Monrovia Christian Fellowship', 'Pastor Francis T. Neufville',
            'Tuesday, November 25, 2025', 'Commencement Convocation 2025', '10:00 a.m.', 'Monrovia Christian Fellowship', 'Guest Speaker', 'Hon. Christine N. Umutoni',
            'Red &amp; White', 'All guests must be in the Main Hall by 9:00 a.m.',
        ], escape: false);
        $this->assertSame(4, substr_count($response->getContent(), 'data-calendar-entry'));
    }

    public function test_the_previous_years_dates_are_flagged_until_confirmed(): void
    {
        $this->get(route('protocols.show'))
            ->assertSee('Awaiting confirmation for the Class of 2026.')
            ->assertSee('from the 2025 graduation program')
            ->assertSee('2025 date')
            ->assertSee('to be confirmed');

        config(['schedule.confirmed' => true]);

        $this->get(route('protocols.show'))
            ->assertDontSee('Awaiting confirmation')
            ->assertDontSee('to be confirmed');
    }

    public function test_rsvp_remains_available_elsewhere(): void
    {
        $this->get(route('rsvp.create'))->assertOk();
        $this->get(route('events.show', 'honors-ball'))->assertSee(route('rsvp.create'));
        $this->get(route('landing'))->assertSee(route('rsvp.create')); // the header's RSVP button
    }
}
