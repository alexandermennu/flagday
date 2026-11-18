<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A Convocation program PDF part-way through a chunked upload. Each chunk received is
 * stored as a ProgramPdfPart; saving the program form moves them onto its PDF.
 */
class ProgramPdfUpload extends Model
{
    protected $fillable = ['token', 'total_size', 'received_size'];

    protected function casts(): array
    {
        return ['total_size' => 'integer', 'received_size' => 'integer'];
    }

    public function isComplete(): bool
    {
        return $this->received_size === $this->total_size;
    }
}
