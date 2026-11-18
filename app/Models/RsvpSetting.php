<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

/** Whether the Honors Ball RSVP is open (admin → Dashboard). */
class RsvpSetting extends Model
{
    protected $fillable = ['is_open'];

    protected function casts(): array
    {
        return ['is_open' => 'boolean'];
    }

    public static function current(): self
    {
        return static::query()->first() ?? static::create(['is_open' => true]);
    }

    public static function isOpen(): bool
    {
        try {
            return static::current()->is_open;
        } catch (QueryException) {
            // The settings table doesn't exist yet (migrations not run): RSVP stays open, as before.
            return true;
        }
    }
}
