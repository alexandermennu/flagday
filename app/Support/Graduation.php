<?php

namespace App\Support;

use App\Models\Program;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Read-side helpers for the graduation event content in config/graduation.php and the
 * RSVP event in config/event.php. When an event has a published program (admin →
 * Programs), its date, time, venue and venue address come from that program record
 * instead of config. Any detail that hasn't been set renders as a "To be announced"
 * placeholder rather than a guessed value.
 */
class Graduation
{
    public const PLACEHOLDER = 'To be announced';

    /**
     * @return Collection<string, array<string, mixed>>
     */
    public static function events(): Collection
    {
        $published = self::publishedPrograms();

        return collect(config('graduation.events', []))
            ->map(fn (array $event, string $slug) => self::present($slug, $event, $published->get($slug)));
    }

    /**
     * The published program for each event, keyed by type — the same program
     * Program::publishedFor() serves the PDF of (latest published wins). Only the columns
     * the public pages need are loaded; the PDF itself is never pulled in here.
     *
     * @return Collection<string, Program>
     */
    private static function publishedPrograms(): Collection
    {
        try {
            return Program::query()
                ->select(['id', 'type', 'event_date', 'event_time', 'venue_name', 'venue_address', 'published_at'])
                ->whereNotNull('published_at')
                ->whereHas('pdf', fn ($query) => $query->whereNotNull('published_data'))
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->get()
                ->unique('type')
                ->keyBy('type');
        } catch (QueryException) {
            // The programs tables don't exist yet (migrations not run): fall back to config.
            return collect();
        }
    }

    public static function find(string $slug): ?array
    {
        return self::events()->get($slug);
    }

    public static function formatDate(?string $date, string $format = 'l, F j, Y'): string
    {
        return filled($date) ? date($format, strtotime($date)) : self::PLACEHOLDER;
    }

    public static function formatTime(?string $time): string
    {
        return filled($time) ? date('g:i A', strtotime($time)) : self::PLACEHOLDER;
    }

    public static function orPlaceholder(?string ...$parts): string
    {
        $value = collect($parts)->filter(fn ($part) => filled($part))->implode(', ');

        return $value !== '' ? $value : self::PLACEHOLDER;
    }

    /**
     * Whether the RSVP event has a concrete date and start time — the calendar file
     * can't be built without them, so the "Add to Calendar" link is hidden until then.
     */
    public static function rsvpEventIsScheduled(): bool
    {
        return filled(config('event.date')) && filled(config('event.start_time'));
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    private static function present(string $slug, array $event, ?Program $published = null): array
    {
        $pdf = $event['program_pdf'] ?? null;
        $configUrl = filled($pdf) ? (Str::startsWith($pdf, ['http://', 'https://']) ? $pdf : asset($pdf)) : null;
        $hasPublishedProgram = $published !== null;

        // A published program's details replace config entirely — an empty field shows
        // "To be announced" rather than mixing in another source. (The program form has no
        // dress code, so that one still comes from config.)
        if ($published) {
            $event = array_merge($event, [
                'date' => $published->event_date?->toDateString(),
                'time' => $published->event_time,
                'venue' => $published->venue_name,
                'venue_address' => $published->venue_address,
            ]);
        }

        return array_merge($event, [
            'slug' => $slug,
            'date_label' => self::formatDate($event['date'] ?? null),
            'time_label' => self::formatTime($event['time'] ?? null),
            'venue_label' => self::orPlaceholder($event['venue'] ?? null),
            'venue_address' => $event['venue_address'] ?? null,
            'dress_code_label' => self::orPlaceholder($event['dress_code'] ?? null),
            'program_url' => $hasPublishedProgram ? route('events.program', $slug) : $configUrl,
            'program_download_url' => $hasPublishedProgram ? route('events.program', [$slug, 'download' => 1]) : null,
            'rsvp' => (bool) ($event['rsvp'] ?? false),
        ]);
    }
}
