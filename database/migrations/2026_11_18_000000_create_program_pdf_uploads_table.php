<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A Convocation program PDF being uploaded in chunks (admin → Programs). Each chunk is
 * appended here as base64 until the file is complete; saving the program form then copies
 * it into program_pdfs inside the database and removes the row. Chunking keeps every
 * request small, so a large PDF isn't held back by PHP, web server or edge body limits.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_pdf_uploads', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->unique();
            $table->unsignedBigInteger('total_size');
            $table->unsignedBigInteger('received_size')->default(0);
            $table->longText('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_pdf_uploads');
    }
};
