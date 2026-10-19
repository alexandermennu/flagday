<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-managed photo galleries. The Honors Ball page shows the "honors-ball" gallery in
 * place of its "More Graduation Events" section. Photo bytes live in gallery_photo_files
 * (in the database, like program_assets/program_pdfs) because Laravel Cloud's local disk
 * is reset on every deploy; listing photos never loads them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique();
            $table->string('title', 120);
            $table->string('subtitle', 300)->nullable();
            $table->boolean('is_visible')->default(false);
            $table->timestamps();
        });

        Schema::create('gallery_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->string('caption', 200)->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->timestamps();

            $table->index(['gallery_id', 'position']);
        });

        Schema::create('gallery_photo_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_photo_id')->unique()->constrained()->cascadeOnDelete();
            $table->longText('full');
            $table->longText('thumb');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_photo_files');
        Schema::dropIfExists('gallery_photos');
        Schema::dropIfExists('galleries');
    }
};
