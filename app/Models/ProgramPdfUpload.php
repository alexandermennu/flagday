<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A Convocation program PDF part-way through a chunked upload. `data` is the base64 of
 * the bytes received so far; it is only ever appended to and copied in SQL, never loaded.
 */
class ProgramPdfUpload extends Model
{
    protected $fillable = ['token', 'total_size', 'received_size', 'data'];

    protected function casts(): array
    {
        return ['total_size' => 'integer', 'received_size' => 'integer'];
    }

    public function isComplete(): bool
    {
        return $this->received_size === $this->total_size;
    }
}
