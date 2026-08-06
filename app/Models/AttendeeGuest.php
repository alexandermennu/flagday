<?php

namespace App\Models;

use Database\Factories\AttendeeGuestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['full_name', 'organization', 'position'])]
class AttendeeGuest extends Model
{
    /** @use HasFactory<AttendeeGuestFactory> */
    use HasFactory;

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }
}
