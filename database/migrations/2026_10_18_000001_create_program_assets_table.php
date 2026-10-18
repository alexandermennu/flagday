<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Uploaded program images (logos, seals, cover art), stored base64-encoded in the
 * database rather than on disk — Laravel Cloud's local filesystem is not persistent
 * across deploys, and this avoids adding an S3 driver just for a handful of logos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('slot', 40);
            $table->string('mime', 40);
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->longText('data');
            $table->timestamps();

            $table->unique(['program_id', 'slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_assets');
    }
};
