<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * A program's PDF: the working copy (last generated or uploaded, used by Download PDF)
 * and the copy frozen at the last publish (served on the public event page). Both are
 * stored as ProgramPdfPart rows rather than single values — see the parts migration.
 */
class ProgramPdf extends Model
{
    public const WORKING = 'working';

    public const PUBLISHED = 'published';

    /**
     * Bytes per stored part. Each part written or read costs the database a few times its
     * size in memory, so parts stay small enough for many at once on a 512 MiB database.
     */
    public const PART_BYTES = 1024 * 1024;

    /** Lock that lets one large PDF write run at a time, bounding the database's memory use. */
    public const WRITE_LOCK = 'program-pdf-writes';

    protected $fillable = ['program_id'];

    public function parts(): HasMany
    {
        return $this->hasMany(ProgramPdfPart::class);
    }

    /** Replaces the working copy with the given PDF. */
    public function storeWorking(string $bytes): void
    {
        $this->parts()->where('variant', self::WORKING)->delete();

        for ($offset = 0; $offset < strlen($bytes); $offset += self::PART_BYTES) {
            $piece = substr($bytes, $offset, self::PART_BYTES);
            $this->parts()->create([
                'variant' => self::WORKING,
                'position' => $offset,
                'size' => strlen($piece),
                'data' => base64_encode($piece),
            ]);
        }
    }

    /** Replaces the working copy with a completed chunked upload, moving its parts across. */
    public function adoptUpload(ProgramPdfUpload $upload): void
    {
        $this->parts()->where('variant', self::WORKING)->delete();

        ProgramPdfPart::where('program_pdf_upload_id', $upload->id)->update([
            'program_pdf_id' => $this->id,
            'program_pdf_upload_id' => null,
            'variant' => self::WORKING,
        ]);
    }

    /** Freezes the working copy as the published copy, copied inside the database. */
    public function publishWorking(): void
    {
        Cache::lock(self::WRITE_LOCK, 120)->block(60, fn () => $this->copyWorkingToPublished());
    }

    private function copyWorkingToPublished(): void
    {
        $this->parts()->where('variant', self::PUBLISHED)->delete();

        DB::table('program_pdf_parts')->insertUsing(
            ['program_pdf_id', 'variant', 'position', 'size', 'data', 'created_at', 'updated_at'],
            DB::table('program_pdf_parts')
                ->where('program_pdf_id', $this->id)
                ->where('variant', self::WORKING)
                ->selectRaw('program_pdf_id, ?, position, size, data, ?, ?', [self::PUBLISHED, now(), now()]),
        );
    }

    /** Limits a query to PDFs that have a published copy. */
    public function scopeHasPublishedCopy(Builder $query): void
    {
        $query->whereHas('parts', fn (Builder $parts) => $parts->where('variant', self::PUBLISHED));
    }

    /** The working copy: last generated (or uploaded) PDF, used by Download PDF. */
    public function bytes(): string
    {
        return $this->assemble(self::WORKING);
    }

    /** The copy frozen at the last publish, served on the public event page. */
    public function publishedBytes(): ?string
    {
        $bytes = $this->assemble(self::PUBLISHED);

        return $bytes === '' ? null : $bytes;
    }

    private function assemble(string $variant): string
    {
        return $this->parts()->where('variant', $variant)->orderBy('position')->pluck('data')
            ->map(fn (string $piece) => base64_decode($piece))
            ->implode('');
    }

    /**
     * A response streaming the program's working or published PDF one part at a time, so
     * a large PDF is never held in memory whole. Null when there is no such PDF.
     *
     * @param  array<string, string>  $headers
     */
    public static function stream(int $programId, string $variant, array $headers): ?StreamedResponse
    {
        $parts = ProgramPdfPart::query()
            ->whereIn('program_pdf_id', static::where('program_id', $programId)->select('id'))
            ->where('variant', $variant)
            ->orderBy('position')
            ->get(['id', 'size']);

        if ($parts->isEmpty()) {
            return null;
        }

        return response()->stream(function () use ($parts) {
            foreach ($parts as $part) {
                echo base64_decode((string) ProgramPdfPart::whereKey($part->id)->value('data'));
                flush();
            }
        }, 200, $headers + ['Content-Length' => (string) $parts->sum('size')]);
    }
}
