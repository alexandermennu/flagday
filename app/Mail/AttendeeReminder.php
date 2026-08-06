<?php

namespace App\Mail;

use App\Models\Attendee;
use App\Services\DigitalInviteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AttendeeReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Attendee $attendee)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reminder — '.config('event.name').' is coming up',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.attendee-reminder',
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
