<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();

            // Event
            $table->string('title', 120);
            $table->string('class_year', 9);
            $table->string('edition_label', 80)->nullable();
            $table->date('event_date')->nullable();
            $table->string('event_time', 5)->nullable();
            $table->string('venue_name', 120)->nullable();
            $table->string('venue_address', 160)->nullable();
            $table->text('description')->nullable();
            $table->string('hosted_by', 120)->nullable();

            // Institution & contact
            $table->string('institution_name', 60);
            $table->string('institution_address_line1', 80)->nullable();
            $table->string('institution_address_line2', 80)->nullable();
            $table->string('contact_phone', 60)->nullable();
            $table->string('contact_email', 160)->nullable();
            $table->string('contact_website', 160)->nullable();

            // "Special Honors Awards" wordmark (cover + honors page)
            $table->string('mark_top', 30)->nullable();
            $table->string('mark_script', 30)->nullable();
            $table->string('mark_bottom', 30)->nullable();

            // Black honors page
            $table->string('honors_line1', 40)->nullable();
            $table->string('honors_ribbon', 40)->nullable();
            $table->string('honors_line2', 40)->nullable();
            $table->string('honors_line3', 40)->nullable();

            // Ordered lists, edited as repeatable rows in the admin form
            $table->json('agenda')->nullable();
            $table->string('awards_heading', 120)->nullable();
            $table->json('awards')->nullable();

            // Publishing / generated PDF metadata (the PDF bytes live in program_pdfs)
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('published_version')->default(0);
            $table->timestamp('pdf_generated_at')->nullable();
            $table->unsignedInteger('pdf_size')->nullable();
            $table->unsignedSmallInteger('pdf_page_count')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
