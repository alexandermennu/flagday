<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Read-side helpers for the graduation event content in config/graduation.php and the
 * RSVP event in config/event.php. Any detail that hasn't been set yet renders as a
 * "To be announced" placeholder rather than a guessed value.
 */
class Graduation
{
    public const PLACEHOLDER = 'To be announced';

    /**
     * @return Collection<string, array<string, mixed>>
     */
    public static function events(): Collection
    {
        return collect(config('graduation.events', []))
            ->map(fn (array $event, string $slug) => self::present($slug, $event));
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
    private static function present(string $slug, array $event): array
    {
        $pdf = $event['program_pdf'] ?? null;

        return array_merge($event, [
            'slug' => $slug,
            'date_label' => self::formatDate($event['date'] ?? null),
            'time_label' => self::formatTime($event['time'] ?? null),
            'venue_label' => self::orPlaceholder($event['venue'] ?? null),
            'venue_address' => $event['venue_address'] ?? null,
            'dress_code_label' => self::orPlaceholder($event['dress_code'] ?? null),
            'program_url' => filled($pdf) ? (Str::startsWith($pdf, ['http://', 'https://']) ? $pdf : asset($pdf)) : null,
            'rsvp' => (bool) ($event['rsvp'] ?? false),
        ]);
    }
}
