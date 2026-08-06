<?php

namespace App\Models;

use App\Enums\AttendeeStatus;
use App\Mail\RsvpConfirmation;
use Database\Factories\AttendeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

#[Fillable(['full_name', 'email', 'phone', 'organization', 'status'])]
class Attendee extends Model
{
    /** @use HasFactory<AttendeeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AttendeeStatus::class,
            'confirmed_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'checked_in_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Attendee $attendee) {
            $attendee->invite_token ??= (string) Str::uuid();
        });
    }

    /**
     * Queue the confirmation email carrying the digital invite (PDF ticket + calendar file).
     * Called from both the public RSVP flow and admin-side manual confirmations, so the
     * "who gets a ticket" rule lives in exactly one place.
     */
    public function sendConfirmationEmail(): void
    {
        Mail::to($this->email)->queue(new RsvpConfirmation($this));
    }
}
