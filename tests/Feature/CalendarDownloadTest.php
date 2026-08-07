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
        $attendee = Attendee::factory()->confirmed()->create();

        $response = $this->get(route('calendar.show', $attendee->invite_token));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $response->assertSee('BEGIN:VCALENDAR', escape: false);
        $response->assertSee(config('event.name'), escape: false);
    }
}
