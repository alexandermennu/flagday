<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A frozen copy of the PDF taken when the program is published. The public event pages
 * serve this copy, so regenerating (or replacing an uploaded file) after publishing does
 * not change what visitors see until the program is published again.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_pdfs', function (Blueprint $table) {
            $table->longText('published_data')->nullable()->after('data');
        });
    }

    public function down(): void
    {
        Schema::table('program_pdfs', function (Blueprint $table) {
            $table->dropColumn('published_data');
        });
    }
};
