<?php

namespace App\Mail;

use App\Models\Attendee;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RsvpDeclinedAcknowledgement extends Mailable
{
    // Sent synchronously — see the note on RsvpConfirmation. Reply-to is applied
    // globally by MailManager (config('mail.reply_to')), not set here.
    public function __construct(public Attendee $attendee)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
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
