<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramAsset extends Model
{
    protected $fillable = ['slot', 'mime', 'width', 'height', 'data'];

    protected $touches = ['program'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function dataUri(): string
    {
        return "data:{$this->mime};base64,{$this->data}";
    }
}
