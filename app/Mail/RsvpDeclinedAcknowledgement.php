<?php

namespace App\Mail;

use App\Mail\Concerns\SendsAsInstitution;
use App\Models\Attendee;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RsvpDeclinedAcknowledgement extends Mailable
{
    use SendsAsInstitution;

    // Sent synchronously — see the note on RsvpConfirmation.
    public function __construct(public Attendee $attendee)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->institutionSender(),
            subject: "We'll miss you — ".config('event.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rsvp-declined',
        );
    }
}
