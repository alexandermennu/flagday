<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Program PDFs are stored as a series of parts (a few MB of base64 each) instead of one
 * column value per copy. A single value can't grow past MySQL's max_allowed_packet — on
 * production a Convocation upload failed once its stored base64 reached 32 MB — so no
 * value may grow with the size of the file.
 *
 * A part belongs either to a program PDF (its working or published copy) or to a chunked
 * upload in progress; saving the program form moves an upload's parts onto the PDF.
 * Existing PDFs are moved into parts here; uploads in progress are discarded.
 */
return new class extends Migration
{
    /** Base64 characters per migrated part — a multiple of 4, so each part decodes on its own. */
    private const PIECE = 4 * 1024 * 1024;

    public function up(): void
    {
        Schema::create('program_pdf_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_pdf_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('program_pdf_upload_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('variant', 10)->nullable(); // 'working' or 'published' (program PDF parts only)
            $table->unsignedBigInteger('position'); // byte offset of this part in the file
            $table->unsignedInteger('size'); // decoded bytes in this part
            $table->mediumText('data'); // base64
            $table->timestamps();

            $table->index(['program_pdf_id', 'variant', 'position']);
            $table->index(['program_pdf_upload_id', 'position']);
        });

        $now = now();
        foreach (DB::table('program_pdfs')->pluck('id') as $id) {
            foreach (['data' => 'working', 'published_data' => 'published'] as $column => $variant) {
                $length = (int) DB::table('program_pdfs')->where('id', $id)->selectRaw("LENGTH({$column}) AS length")->value('length');
                $position = 0;

                for ($offset = 1; $offset <= $length; $offset += self::PIECE) {
                    $piece = (string) DB::table('program_pdfs')->where('id', $id)
                        ->selectRaw("SUBSTR({$column}, ?, ?) AS piece", [$offset, self::PIECE])
                        ->value('piece');
                    $size = strlen(base64_decode($piece));

                    DB::table('program_pdf_parts')->insert([
                        'program_pdf_id' => $id, 'variant' => $variant, 'position' => $position, 'size' => $size,
                        'data' => $piece, 'created_at' => $now, 'updated_at' => $now,
                    ]);
                    $position += $size;
                }
            }
        }

        Schema::table('program_pdfs', function (Blueprint $table) {
            $table->dropColumn(['data', 'published_data']);
        });

        DB::table('program_pdf_uploads')->delete();
        Schema::table('program_pdf_uploads', function (Blueprint $table) {
            $table->dropColumn('data');
        });
    }

    public function down(): void
    {
        Schema::table('program_pdf_uploads', function (Blueprint $table) {
            $table->longText('data')->nullable();
        });

        Schema::table('program_pdfs', function (Blueprint $table) {
            $table->longText('data')->nullable();
            $table->longText('published_data')->nullable();
        });

        foreach (DB::table('program_pdfs')->pluck('id') as $id) {
            $copy = fn (string $variant) => DB::table('program_pdf_parts')
                ->where('program_pdf_id', $id)->where('variant', $variant)->orderBy('position')->pluck('data')
                ->map(fn ($piece) => base64_decode($piece))->implode('');

            $published = $copy('published');
            DB::table('program_pdfs')->where('id', $id)->update([
                'data' => base64_encode($copy('working')),
                'published_data' => $published === '' ? null : base64_encode($published),
            ]);
        }

        Schema::dropIfExists('program_pdf_parts');
    }
};
