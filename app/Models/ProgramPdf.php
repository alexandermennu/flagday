<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramPdf extends Model
{
    protected $fillable = ['program_id', 'data'];

    public function bytes(): string
    {
        return base64_decode($this->data);
    }
}
