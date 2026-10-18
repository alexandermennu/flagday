<?php

namespace App\Mail;

use App\Mail\Concerns\SendsAsInstitution;
use App\Models\Attendee;
use App\Services\DigitalInviteService;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RsvpConfirmation extends Mailable
{
    use SendsAsInstitution;

    // Sent synchronously (see Attendee::sendConfirmationEmail()) rather than queued —
    // this is the confirmation on the critical RSVP path, and queuing it makes delivery
    // silently depend on a queue worker actually running in production.
    //
    // No reply-to is set, so replies go to the From address.
    public function __construct(public Attendee $attendee)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->institutionSender(),
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
            Attachment::fromData(fn () => $invites->pdf($this->attendee), 'Starz_University_Honors_Ball_Event_Pass.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
