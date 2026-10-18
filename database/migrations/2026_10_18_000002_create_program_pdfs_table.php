<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The most recently generated PDF for each program (base64), kept in the database for
 * the same persistence reason as program_assets. Regenerated only on an explicit
 * "Generate PDF" or "Publish" action, and served as-is by "Download PDF".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_pdfs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->unique()->constrained()->cascadeOnDelete();
            $table->longText('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_pdfs');
    }
};
