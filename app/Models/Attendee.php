<?php

namespace App\Models;

use App\Enums\AttendeeStatus;
use App\Mail\RsvpConfirmation;
use Database\Factories\AttendeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

#[Fillable(['first_name', 'last_name', 'email', 'phone', 'organization', 'department', 'position', 'decline_reason', 'status'])]
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

    /**
     * Computed full name, kept as a read-only "full_name" attribute so PDF/email/CSV
     * templates that display a single name field don't need to know about the split.
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn () => trim("{$this->first_name} {$this->last_name}"));
    }

    protected static function booted(): void
    {
        static::creating(function (Attendee $attendee) {
            $attendee->invite_token ??= (string) Str::uuid();
        });
    }

    public function guests(): HasMany
    {
        return $this->hasMany(AttendeeGuest::class);
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
