{{--
    Shared public site header — used by every public marketing page (landing, flag-day) so
    the nav's item set, order, and "The 179th Flag Day" position never drift apart between
    pages again. "About", "Gallery", and "Contact" are anchors that only exist on the
    flag-day page; from any other page they resolve to a full URL + hash, from the flag-day
    page itself the fragment-only link just scrolls (no reload).
--}}
@php
    $isFlagDay = request()->routeIs('flag-day.show');
    $aboutHref = $isFlagDay ? '#about' : route('flag-day.show').'#about';
    $galleryHref = $isFlagDay ? '#gallery' : route('flag-day.show').'#gallery';
    $contactHref = $isFlagDay ? '#contact' : route('flag-day.show').'#contact';

    $navItems = [
        ['label' => 'Home', 'href' => route('landing'), 'active' => request()->routeIs('landing')],
        ['label' => 'About', 'href' => $aboutHref, 'active' => false],
        ['label' => 'The 179th Flag Day', 'href' => route('flag-day.show'), 'active' => $isFlagDay],
        ['label' => 'Gallery', 'href' => $galleryHref, 'active' => false],
        ['label' => 'RSVP', 'href' => route('rsvp.create'), 'active' => false],
        ['label' => 'Contact', 'href' => $contactHref, 'active' => false],
    ];
@endphp

<header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-3 lg:px-8">
        <a href="{{ route('landing') }}" class="flex items-center gap-3">
            <x-seal class="h-10 w-10 shrink-0" />
            <span class="leading-tight">
                <span class="block text-[11px] font-semibold uppercase tracking-wider text-red-700">Republic of Liberia</span>
                <span class="block text-sm font-bold text-blue-950">Ministry of Education</span>
            </span>
        </a>

        <nav class="hidden items-center gap-7 md:flex" aria-label="Primary">
            @foreach ($navItems as $item)
                <a href="{{ $item['href'] }}"
                   @if ($item['active']) aria-current="page" @endif
                   class="text-sm {{ $item['active'] ? 'border-b-2 border-red-700 pb-0.5 font-semibold text-blue-950' : 'font-medium text-slate-600 transition hover:text-blue-950' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="flex items-center gap-3">
            <a href="{{ route('rsvp.create') }}"
               class="hidden items-center gap-2 rounded-md bg-red-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-800 active:scale-[0.98] sm:inline-flex">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <rect x="3" y="4.5" width="18" height="16" rx="2" />
                    <path d="M3 9.5h18M8 3v3M16 3v3" />
                </svg>
                RSVP Now
            </a>

            <button type="button" id="mobile-menu-toggle" aria-expanded="false" aria-controls="mobile-menu"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-slate-300 text-slate-600 md:hidden">
                <span class="sr-only">Toggle menu</span>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
        </div>
    </div>

    <div id="mobile-menu" class="hidden border-t border-slate-200 bg-white px-6 py-4 md:hidden">
        <nav class="flex flex-col gap-1" aria-label="Mobile">
            @foreach ($navItems as $item)
                <a href="{{ $item['href'] }}"
                   class="rounded-md px-3 py-2.5 text-sm font-medium {{ $item['active'] ? 'bg-red-50 font-semibold text-red-700' : 'text-slate-600 hover:bg-slate-50' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</header>
