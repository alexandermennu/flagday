<?php

namespace App\Console\Commands;

use App\Enums\AttendeeStatus;
use App\Mail\RsvpConfirmation;
use App\Models\Attendee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SendTestRsvpEmail extends Command
{
    protected $signature = 'mail:test-rsvp {email}';

    protected $description = 'Send a real RSVP confirmation email (with PDF ticket + calendar file) to the given address, without touching the database — for verifying mail delivery end-to-end';

    public function handle(): int
    {
        $email = $this->argument('email');

        $validator = Validator::make(['email' => $email], ['email' => ['required', 'email']]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        // Deliberately not persisted — this is a throwaway model instance used only to
        // render the mailable, so running this command never creates a fake attendee record.
        $attendee = new Attendee([
            'first_name' => 'Test',
            'last_name' => 'Recipient',
            'organization' => 'Ministry of Education',
            'position' => 'Mail Delivery Test',
            'status' => AttendeeStatus::Confirmed,
        ]);
        $attendee->email = $email;
        $attendee->invite_token = (string) Str::uuid();
        $attendee->confirmation_id = 'TEST-'.strtoupper(Str::random(6));
        $attendee->confirmed_at = now();

        $mailer = config('mail.default');
        $this->info("Sending test RSVP confirmation to {$email} via the [{$mailer}] mailer...");

        try {
            Mail::to($email)->send(new RsvpConfirmation($attendee));
        } catch (\Throwable $e) {
            $this->error('Send failed: '.$e::class.' — '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Sent. Check the inbox (and spam folder) for {$email}.");

        return self::SUCCESS;
    }
}
