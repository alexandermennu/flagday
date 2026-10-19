{{--
    The event's program card: view (flipbook or PDF) and download the published program,
    or "coming soon". Expects $event; $surface optionally overrides the card background
    (the Honors Ball shows it inside the dark hero, where it needs a border to stand out).
--}}
<div data-program-card class="rounded-2xl p-6 text-white {{ $surface ?? 'bg-ink' }}">
    <h2 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gold-400">Event Program</h2>
    @if ($event['program_url'])
        <p class="mt-3 text-sm text-neutral-300">Download the official program for the {{ $event['name'] }}.</p>
        @if ($event['flipbook_url'])
            <a href="{{ $event['flipbook_url'] }}"
               class="mt-5 inline-flex w-full items-center justify-center rounded-full bg-gold-400 px-5 py-3 text-sm font-semibold text-ink transition hover:bg-gold-300">
                View Program
            </a>
        @else
            <a href="{{ $event['program_url'] }}" target="_blank" rel="noopener"
               class="mt-5 inline-flex w-full items-center justify-center rounded-full bg-gold-400 px-5 py-3 text-sm font-semibold text-ink transition hover:bg-gold-300">
                View Program (PDF)
            </a>
        @endif
        @if ($event['program_download_url'])
            <a href="{{ $event['program_download_url'] }}"
               class="mt-3 inline-flex w-full items-center justify-center rounded-full border border-white/20 px-5 py-3 text-sm font-semibold text-white transition hover:border-gold-400 hover:text-gold-300">
                Download PDF
            </a>
        @endif
    @else
        <p class="mt-3 text-sm text-neutral-300">The program for this event will be available here soon.</p>
        <span class="mt-5 inline-flex w-full items-center justify-center rounded-full border border-white/15 px-5 py-3 text-sm font-semibold text-neutral-400">
            Program coming soon
        </span>
    @endif
</div>
