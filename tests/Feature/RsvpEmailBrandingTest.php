<?php

namespace Tests\Feature;

use App\Mail\AttendeeReminder;
use App\Mail\RsvpConfirmation;
use App\Mail\RsvpDeclinedAcknowledgement;
use App\Models\Attendee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

class RsvpEmailBrandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mimic an environment still carrying the old Flag Day mail settings.
        config([
            'mail.default' => 'array',
            'mail.from.address' => 'noreply@example.test',
            'mail.from.name' => 'National Flag Day RSVP',
        ]);
    }

    public function test_a_leftover_reply_to_env_var_is_ignored(): void
    {
        // Production may still define MAIL_REPLY_TO_ADDRESS (the old Ministry inbox);
        // the config no longer reads it, so it can't be applied to outgoing mail.
        putenv('MAIL_REPLY_TO_ADDRESS=old-inbox@example.test');
        $_ENV['MAIL_REPLY_TO_ADDRESS'] = $_SERVER['MAIL_REPLY_TO_ADDRESS'] = 'old-inbox@example.test';

        try {
            $mailConfig = require config_path('mail.php');
        } finally {
            putenv('MAIL_REPLY_TO_ADDRESS');
            unset($_ENV['MAIL_REPLY_TO_ADDRESS'], $_SERVER['MAIL_REPLY_TO_ADDRESS']);
        }

        $this->assertArrayNotHasKey('reply_to', $mailConfig);
        $this->assertNull(config('mail.reply_to'));
    }

    private function sendAndCapture(object $mailable): Email
    {
        Mail::to('guest@example.com')->send($mailable);

        $messages = Mail::mailer('array')->getSymfonyTransport()->messages();

        return $messages->last()->getOriginalMessage();
    }

    public function test_confirmation_email_sends_as_starz_university_with_no_reply_to(): void
    {
        $attendee = Attendee::factory()->confirmed()->create();

        $email = $this->sendAndCapture(new RsvpConfirmation($attendee));

        $this->assertSame('Starz University', $email->getFrom()[0]->getName());
        $this->assertSame('noreply@example.test', $email->getFrom()[0]->getAddress());
        $this->assertSame([], $email->getReplyTo());
    }

    public function test_confirmation_email_keeps_the_event_pass_and_drops_old_branding(): void
    {
        $attendee = Attendee::factory()->confirmed()->create();

        $email = $this->sendAndCapture(new RsvpConfirmation($attendee));

        $attachments = $email->getAttachments();
        $this->assertCount(1, $attachments);
        $this->assertSame('Starz_University_Honors_Ball_Event_Pass.pdf', $attachments[0]->getFilename());
        $this->assertStringStartsWith('%PDF', $attachments[0]->getBody());

        $html = $email->getHtmlBody();
        $this->assertStringContainsString('Starz University', $html);
        $this->assertStringContainsString($attendee->full_name, $html);
        foreach (['Ministry', 'Flag Day', 'Liberia', 'moe.gov'] as $old) {
            $this->assertStringNotContainsStringIgnoringCase($old, $html.$email->getSubject());
        }
    }

    public function test_decline_and_reminder_emails_use_the_same_sender(): void
    {
        $attendee = Attendee::factory()->confirmed()->create();

        foreach ([new RsvpDeclinedAcknowledgement($attendee), new AttendeeReminder($attendee)] as $mailable) {
            $email = $this->sendAndCapture($mailable);

            $this->assertSame('Starz University', $email->getFrom()[0]->getName());
            $this->assertSame([], $email->getReplyTo());
        }
    }
}
