<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * A Convocation program PDF part-way through a chunked upload. Each chunk received is
 * stored as a ProgramPdfPart; saving the program form moves them onto its PDF.
 */
class ProgramPdfUpload extends Model
{
    use MassPrunable;

    protected $fillable = ['token', 'total_size', 'received_size'];

    protected function casts(): array
    {
        return ['total_size' => 'integer', 'received_size' => 'integer'];
    }

    /** Uploads not touched for a day: abandoned, or failed part-way. Their parts go with them (cascade). */
    public function scopeAbandoned(Builder $query): void
    {
        $query->where('updated_at', '<', now()->subDay());
    }

    public function prunable(): Builder
    {
        return static::abandoned();
    }

    public function isComplete(): bool
    {
        return $this->received_size === $this->total_size;
    }
}
