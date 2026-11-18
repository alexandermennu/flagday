<?php

use Illuminate\Foundation\Inspiring;
use App\Models\ProgramPdfUpload;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Program PDF uploads that were started but never saved, with their stored parts.
Schedule::command('model:prune', ['--model' => [ProgramPdfUpload::class]])->daily();
