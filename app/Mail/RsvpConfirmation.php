<?php

namespace App\Mail;

use App\Models\Attendee;
use App\Services\DigitalInviteService;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RsvpConfirmation extends Mailable
{
    // Sent synchronously (see Attendee::sendConfirmationEmail()) rather than queued —
    // this is the confirmation on the critical RSVP path, and queuing it makes delivery
    // silently depend on a queue worker actually running in production.
    public function __construct(public Attendee $attendee)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're confirmed — ".config('event.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rsvp-confirmation',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $invites = app(DigitalInviteService::class);

        return [
            Attachment::fromData(fn () => $invites->pdf($this->attendee), 'flag-day-ticket.pdf')
                ->withMime('application/pdf'),
            Attachment::fromData(fn () => $invites->ics($this->attendee), 'flag-day-invite.ics')
                ->withMime('text/calendar'),
        ];
    }
}
