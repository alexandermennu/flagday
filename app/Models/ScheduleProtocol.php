<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** One graduation-day protocol step (time label + description) on the Protocols & Calendar page. */
class ScheduleProtocol extends Model
{
    protected $fillable = ['time_label', 'description', 'position', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }
}
