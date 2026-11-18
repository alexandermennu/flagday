<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A few MB (base64) of a program PDF's working or published copy, or of a chunked upload
 * in progress. Stored in parts so no database value grows with the file.
 */
class ProgramPdfPart extends Model
{
    protected $fillable = ['program_pdf_id', 'program_pdf_upload_id', 'variant', 'position', 'size', 'data'];

    protected function casts(): array
    {
        return ['position' => 'integer', 'size' => 'integer'];
    }
}
