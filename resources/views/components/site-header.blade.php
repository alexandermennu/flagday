{{--
    Shared public site header — used by every public page so the nav's item set and order
    never drift apart. "Events" is an anchor on the landing page; from any other page it
    resolves to the landing URL + hash.
--}}
@php
    $isLanding = request()->routeIs('landing');
    $currentSlug = request()->route('slug');

    $navItems = [
        ['label' => 'Home', 'href' => route('landing'), 'active' => $isLanding],
        ['label' => 'Events', 'href' => ($isLanding ? '' : route('landing')).'#events', 'active' => false],
    ];

    foreach (\App\Support\Graduation::events() as $slug => $event) {
        $navItems[] = ['label' => $event['name'], 'href' => route('events.show', $slug), 'active' => $currentSlug === $slug];
    }
@endphp

<header class="sticky top-0 z-40 border-b border-white/10 bg-ink/95 text-white backdrop-blur" style="top: env(safe-area-inset-top, 0px)">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-3 lg:px-8">
        <a href="{{ route('landing') }}" class="flex min-w-0 items-center gap-3">
            <x-seal class="h-10 w-10 shrink-0" />
            <span class="min-w-0 leading-tight">
                <span class="block truncate font-display text-base font-semibold tracking-wide text-white">{{ config('graduation.institution') }}</span>
                <span class="block text-[11px] font-semibold uppercase tracking-[0.2em] text-gold-400">Class of {{ config('graduation.class_year') }}</span>
            </span>
        </a>

        <nav class="hidden items-center gap-7 lg:flex" aria-label="Primary">
            @foreach ($navItems as $item)
                <a href="{{ $item['href'] }}"
                   @if ($item['active']) aria-current="page" @endif
                   class="text-sm transition {{ $item['active'] ? 'border-b-2 border-gold-400 pb-0.5 font-semibold text-white' : 'font-medium text-neutral-300 hover:text-gold-300' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="flex shrink-0 items-center gap-3">
            <a href="{{ route('rsvp.create') }}"
               class="hidden items-center gap-2 rounded-full bg-gold-400 px-5 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:bg-gold-300 active:scale-[0.98] sm:inline-flex">
                RSVP &middot; Honors Ball
            </a>

            <button type="button" id="mobile-menu-toggle" aria-expanded="false" aria-controls="mobile-menu"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/20 text-white lg:hidden">
                <span class="sr-only">Toggle menu</span>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
        </div>
    </div>

    <div id="mobile-menu" class="hidden border-t border-white/10 bg-ink px-5 py-4 lg:hidden">
        <nav class="flex flex-col gap-1" aria-label="Mobile">
            @foreach ($navItems as $item)
                <a href="{{ $item['href'] }}"
                   class="rounded-lg px-3 py-3 text-base {{ $item['active'] ? 'bg-white/10 font-semibold text-gold-300' : 'font-medium text-neutral-200 hover:bg-white/5' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
            <a href="{{ route('rsvp.create') }}"
               class="mt-3 rounded-full bg-gold-400 px-5 py-3 text-center text-base font-semibold text-ink">
                RSVP for the Honors Ball
            </a>
        </nav>
    </div>
</header>
