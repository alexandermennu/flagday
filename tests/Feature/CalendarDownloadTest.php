<?php

namespace Tests\Feature;

use App\Models\Attendee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_file_is_publicly_downloadable_without_authentication(): void
    {
        config(['event.date' => '2026-12-05', 'event.start_time' => '18:00']);
        $attendee = Attendee::factory()->confirmed()->create();

        $response = $this->get(route('calendar.show', $attendee->invite_token));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $response->assertSee('BEGIN:VCALENDAR', escape: false);
        $response->assertSee(config('event.name'), escape: false);
        $response->assertSee('DTSTART:20261205T180000Z', escape: false);
    }

    public function test_calendar_file_is_unavailable_until_the_event_is_scheduled(): void
    {
        config(['event.date' => null, 'event.start_time' => null]);
        $attendee = Attendee::factory()->confirmed()->create();

        $this->get(route('calendar.show', $attendee->invite_token))->assertNotFound();
    }
}
