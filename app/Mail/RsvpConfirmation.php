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
    //
    // No explicit reply-to here — config('mail.reply_to') is applied globally to every
    // mailer automatically by Illuminate\Mail\MailManager (same mechanism as the global
    // "from" address), so setting it per-Mailable would just double it up.
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
     * Only the PDF is attached — the .ics file is served via a link in the email body
     * instead (see CalendarController), since an attached .ics makes Gmail and some
     * other clients render a large inline event card at the top of the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $invites = app(DigitalInviteService::class);

        return [
            Attachment::fromData(fn () => $invites->pdf($this->attendee), '179th_National_Flag_Day_Event_Pass.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
