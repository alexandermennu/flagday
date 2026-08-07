<?php

namespace App\Services;

use App\Models\Attendee;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class DigitalInviteService
{
    /**
     * Render the attendee's PDF ticket (with embedded QR check-in code) as raw PDF bytes.
     */
    public function pdf(Attendee $attendee): string
    {
        return Pdf::loadView('pdf.ticket', [
            'attendee' => $attendee,
            'qrDataUri' => $this->qrCodeDataUri($attendee),
        ])->setPaper('a5', 'portrait')->output();
    }

    /**
     * Build an iCalendar (.ics) file for the event, keyed to a stable UID so a re-sent
     * invite (e.g. from a reminder email) updates the guest's existing calendar entry
     * instead of creating a duplicate.
     */
    public function ics(Attendee $attendee): string
    {
        $timezone = config('event.timezone');
        $start = Carbon::parse(config('event.date').' '.config('event.start_time'), $timezone)->setTimezone('UTC');
        $end = Carbon::parse(config('event.date').' '.config('event.end_time'), $timezone)->setTimezone('UTC');
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?: 'flagday.local';

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Republic of Liberia//National Flag Day RSVP//EN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.sprintf('attendee-%d@%s', $attendee->id, $host),
            'DTSTAMP:'.Carbon::now('UTC')->format('Ymd\THis\Z'),
            'DTSTART:'.$start->format('Ymd\THis\Z'),
            'DTEND:'.$end->format('Ymd\THis\Z'),
            'SUMMARY:'.$this->escapeIcsText(config('event.name')),
            'LOCATION:'.$this->escapeIcsText(config('event.venue').', '.config('event.venue_address')),
            'DESCRIPTION:'.$this->escapeIcsText(
                "You're confirmed for ".config('event.name').'. Present your digital ticket at the entrance.'
            ),
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", $lines)."\r\n";
    }

    protected function qrCodeDataUri(Attendee $attendee): string
    {
        // An absolute https:// URL (see AppServiceProvider::boot()) so scanning apps
        // recognize it as a link and open it directly, with no copy/paste step.
        $qrCode = new QrCode(
            data: route('pass.verify', $attendee->invite_token),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
        );

        return (new PngWriter)->write($qrCode)->getDataUri();
    }

    protected function escapeIcsText(string $value): string
    {
        return str_replace(
            ["\\", "\n", ',', ';'],
            ['\\\\', '\\n', '\\,', '\\;'],
            $value
        );
    }
}
