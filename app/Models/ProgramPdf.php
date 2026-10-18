<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramPdf extends Model
{
    protected $fillable = ['program_id', 'data', 'published_data'];

    /** The working copy: last generated (or uploaded) PDF, used by Download PDF. */
    public function bytes(): string
    {
        return base64_decode($this->data);
    }

    /** The copy frozen at the last publish, served on the public event page. */
    public function publishedBytes(): ?string
    {
        return $this->published_data === null ? null : base64_decode($this->published_data);
    }
}
