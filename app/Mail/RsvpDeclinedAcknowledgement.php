<?php

namespace App\Mail;

use App\Models\Attendee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RsvpDeclinedAcknowledgement extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

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
