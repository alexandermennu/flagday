<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProgramPdf extends Model
{
    /** Base64 characters read per query when streaming — a multiple of 4, so each piece decodes on its own. */
    private const STREAM_PIECE = 4 * 1024 * 1024;

    protected $fillable = ['program_id', 'data', 'published_data'];

    /** The working copy: last generated (or uploaded) PDF, used by Download PDF. */
    public function bytes(): string
    {
        return base64_decode($this->data);
    }

    /** The copy frozen at the last publish, served on the public event page. */
    public function publishedBytes(): ?string
    {
        return $this->published_data === null ? null : base64_decode($this->published_data);
    }

    /**
     * A response streaming the program's working ('data') or published ('published_data')
     * PDF, decoded a piece at a time so a large PDF is never held in memory whole. Null
     * when there is no such PDF.
     *
     * @param  array<string, string>  $headers
     */
    public static function stream(int $programId, string $column, array $headers): ?StreamedResponse
    {
        $column = match ($column) {
            'data', 'published_data' => $column,
        };

        $row = static::query()->toBase()
            ->where('program_id', $programId)
            ->whereNotNull($column)
            ->selectRaw("id, LENGTH({$column}) AS encoded_length")
            ->first();

        if ($row === null) {
            return null;
        }

        $id = $row->id;
        $encodedLength = (int) $row->encoded_length;
        $piece = fn (int $offset, int $length) => (string) static::query()->toBase()
            ->where('id', $id)
            ->selectRaw("SUBSTR({$column}, ?, ?) AS piece", [$offset, $length])
            ->value('piece');

        $padding = $encodedLength >= 2 ? substr_count($piece($encodedLength - 1, 2), '=') : 0;

        return response()->stream(function () use ($piece, $encodedLength) {
            for ($offset = 1; $offset <= $encodedLength; $offset += self::STREAM_PIECE) {
                echo base64_decode($piece($offset, self::STREAM_PIECE));
                flush();
            }
        }, 200, $headers + ['Content-Length' => (string) (intdiv($encodedLength, 4) * 3 - $padding)]);
    }
}
