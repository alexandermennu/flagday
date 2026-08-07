<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Services\DigitalInviteService;
use Illuminate\Http\Response;

/**
 * Serves the .ics calendar file on demand via a link rather than as an email
 * attachment — Gmail (and some other clients) render an attached .ics as a large
 * inline event card at the top of the message, which this avoids.
 */
class CalendarController extends Controller
{
    public function show(Attendee $attendee, DigitalInviteService $invites): Response
    {
        return response($invites->ics($attendee), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="179th-national-flag-day.ics"',
        ]);
    }
}
