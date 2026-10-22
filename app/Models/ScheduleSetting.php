<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Page-level settings for Protocols & Calendar: the calendar theme and the confirmation notice. */
class ScheduleSetting extends Model
{
    protected $fillable = ['theme', 'is_confirmed', 'source_year'];

    protected function casts(): array
    {
        return ['is_confirmed' => 'boolean'];
    }

    public static function current(): self
    {
        return static::query()->first() ?? static::create(['is_confirmed' => false]);
    }
}
