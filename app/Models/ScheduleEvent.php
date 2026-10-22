<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** One entry in the Calendar of Events on the Protocols & Calendar page. */
class ScheduleEvent extends Model
{
    protected $fillable = [
        'title', 'event_date', 'start_time', 'end_time', 'time_note', 'venue', 'theme', 'speaker_label', 'speaker',
        'guest_of_honor', 'attendance', 'dress_code_label', 'dress_code', 'description', 'notes', 'position', 'is_published',
    ];

    protected function casts(): array
    {
        return ['event_date' => 'date', 'notes' => 'array', 'is_published' => 'boolean'];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    /** "Friday, November 14, 2025" — the source program's date style. */
    public function dateLabel(): ?string
    {
        return $this->event_date?->format('l, F j, Y');
    }

    /** "3:30 p.m.", "7:30 p.m. Prompt", or "10:00 a.m. – 12:00 p.m.". */
    public function timeLabel(): ?string
    {
        if (! $this->start_time) {
            return $this->time_note;
        }

        $label = self::formatTime($this->start_time);
        if ($this->end_time) {
            $label .= ' – '.self::formatTime($this->end_time);
        }

        return trim($label.' '.($this->time_note ?? ''));
    }

    private static function formatTime(string $time): string
    {
        return str_replace(['am', 'pm'], ['a.m.', 'p.m.'], date('g:i a', strtotime($time)));
    }
}
