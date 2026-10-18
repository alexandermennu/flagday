{{-- One graduation event as a card. Expects $event (from App\Support\Graduation) and $index. --}}
<article class="group relative flex flex-col overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-gold-400 hover:shadow-xl hover:shadow-gold-500/10">
    <div class="relative bg-ink px-6 pb-6 pt-7 text-white">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_85%_0%,rgba(231,185,58,0.28),transparent_60%)]"></div>
        <div class="relative flex items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gold-400">{{ $event['kicker'] }}</p>
                <h3 class="mt-2 font-display text-3xl font-semibold">{{ $event['name'] }}</h3>
            </div>
            <span class="font-display text-4xl font-semibold text-white/15" aria-hidden="true">0{{ $index }}</span>
        </div>
        @if ($event['rsvp'])
            <span class="relative mt-4 inline-flex items-center gap-1.5 rounded-full border border-gold-400/50 bg-gold-400/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-gold-300">
                <span class="h-1.5 w-1.5 rounded-full bg-gold-400"></span> RSVP open
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col px-6 py-6">
        <p class="text-sm leading-relaxed text-neutral-600">{{ $event['summary'] }}</p>

        <dl class="mt-5 space-y-3 border-t border-neutral-100 pt-5 text-sm">
            <div class="flex items-start gap-3">
                <dt class="w-14 shrink-0 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 pt-0.5">Date</dt>
                <dd class="font-semibold {{ $event['date'] ? 'text-neutral-900' : 'text-neutral-400 italic font-medium' }}">{{ $event['date_label'] }}</dd>
            </div>
            <div class="flex items-start gap-3">
                <dt class="w-14 shrink-0 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 pt-0.5">Time</dt>
                <dd class="font-semibold {{ $event['time'] ? 'text-neutral-900' : 'text-neutral-400 italic font-medium' }}">{{ $event['time_label'] }}</dd>
            </div>
            <div class="flex items-start gap-3">
                <dt class="w-14 shrink-0 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 pt-0.5">Venue</dt>
                <dd class="font-semibold {{ $event['venue'] ? 'text-neutral-900' : 'text-neutral-400 italic font-medium' }}">{{ $event['venue_label'] }}</dd>
            </div>
        </dl>

        <div class="mt-auto flex flex-col gap-3 pt-6 sm:flex-row lg:flex-col xl:flex-row">
            @if ($event['rsvp'])
                <a href="{{ route('rsvp.create') }}"
                   class="inline-flex flex-1 items-center justify-center rounded-full bg-gold-400 px-5 py-3 text-sm font-semibold text-ink transition hover:bg-gold-300 active:scale-[0.98]">
                    RSVP Now
                </a>
            @endif
            <a href="{{ route('events.show', $event['slug']) }}"
               class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-full border border-neutral-300 px-5 py-3 text-sm font-semibold text-neutral-900 transition hover:border-ink hover:bg-ink hover:text-white">
                Event Details
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </div>
</article>
