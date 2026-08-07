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
            $attendee->confirmation_id ??= static::generateConfirmationId();
        });
    }

    /**
     * A short, human-presentable code (e.g. "NFD179-7X2K8F") shown on the digital ticket
     * — distinct from invite_token, which is the long opaque value encoded in the QR code.
     * Avoids visually ambiguous characters (0/O, 1/I/L) since guests may read it aloud or
     * type it in by hand.
     */
    protected static function generateConfirmationId(): string
    {
        $edition = date('Y', strtotime(config('event.date'))) - 1847;
        $charset = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

        $code = collect(range(1, 6))
            ->map(fn () => $charset[random_int(0, strlen($charset) - 1)])
            ->implode('');

        return "NFD{$edition}-{$code}";
    }

    public function guests(): HasMany
    {
        return $this->hasMany(AttendeeGuest::class);
    }

    /**
     * Send the confirmation email carrying the digital invite (PDF ticket + calendar file).
     * Called from both the public RSVP flow and admin-side manual confirmations, so the
     * "who gets a ticket" rule lives in exactly one place. Sent synchronously — see the
     * note on the RsvpConfirmation mailable.
     */
    public function sendConfirmationEmail(): void
    {
        Mail::to($this->email)->send(new RsvpConfirmation($this));
    }
}
