<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramPdfPart;
use App\Models\ProgramPdfUpload;
use App\Support\UploadLimits;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Chunked upload of a Convocation program PDF (resources/js/admin.js). The browser starts
 * an upload, sends the file in order as raw chunks, then submits the program form with the
 * upload's token in place of the file. Every request stays small, so the size limit is
 * PROGRAM_PDF_MAX_KB alone rather than PHP's, the web server's or the edge's body limits.
 */
class ProgramPdfUploadController extends Controller
{
    /** Largest chunk the browser sends: 4 MB, or less if PHP's limits are lower. */
    private const CHUNK_KB = 4096;

    public function store(Request $request): JsonResponse
    {
        $maxKb = Program::pdfUploadMaxKb();

        $validated = $request->validate(
            ['size' => ['required', 'integer', 'min:1', 'max:'.($maxKb * 1024)]],
            ['size.max' => 'The program PDF may not be larger than '.number_format($maxKb / 1024, 1).' MB.'],
        );

        // Uploads that were started but never saved with the form.
        ProgramPdfUpload::where('updated_at', '<', now()->subDay())->delete();

        $upload = ProgramPdfUpload::create([
            'token' => (string) Str::uuid(),
            'total_size' => (int) $validated['size'],
        ]);

        return response()->json([
            'token' => $upload->token,
            'chunk_size' => self::chunkBytes(),
            'append_url' => route('admin.program-uploads.append', $upload->token),
        ], 201);
    }

    /** Appends the next chunk (the raw request body) at ?offset=, which must be where the last one ended. */
    public function append(Request $request, string $token): JsonResponse
    {
        $upload = ProgramPdfUpload::where('token', $token)->firstOrFail(['id', 'total_size', 'received_size']);
        $offset = $request->integer('offset');
        $bytes = $request->getContent();
        $length = strlen($bytes);
        $remaining = $upload->total_size - $upload->received_size;

        if ($offset !== $upload->received_size || $length === 0 || $length > $remaining) {
            return response()->json(['message' => 'The upload got out of step. Choose the file again and save.'], 409);
        }

        if ($offset === 0 && ! str_starts_with($bytes, '%PDF-')) {
            $upload->delete();

            return response()->json(['message' => 'The program PDF must be a valid PDF file.'], 422);
        }

        try {
            // Each chunk is its own part, so no stored value grows with the file.
            $stored = DB::transaction(function () use ($upload, $offset, $length, $bytes) {
                $claimed = ProgramPdfUpload::whereKey($upload->id)
                    ->where('received_size', $offset)
                    ->update(['received_size' => $offset + $length]);

                if ($claimed) {
                    ProgramPdfPart::create([
                        'program_pdf_upload_id' => $upload->id,
                        'position' => $offset,
                        'size' => $length,
                        'data' => base64_encode($bytes),
                    ]);
                }

                return $claimed > 0;
            });
        } catch (QueryException $e) {
            Log::error('Program PDF upload chunk could not be stored', ['offset' => $offset, 'size' => $length, 'error' => $e->getMessage()]);
            $upload->delete();

            return response()->json(['message' => 'The upload could not be stored. See the server log for details.'], 500);
        }

        if (! $stored) {
            return response()->json(['message' => 'The upload got out of step. Choose the file again and save.'], 409);
        }

        return response()->json(['received' => $offset + $length]);
    }

    /** With headroom under PHP's post_max_size / upload_max_filesize. */
    private static function chunkBytes(): int
    {
        return (int) (UploadLimits::effectiveKb(self::CHUNK_KB) * 1024 * 0.9);
    }
}
