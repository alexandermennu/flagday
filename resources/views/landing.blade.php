<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Join the Government of the Republic of Liberia in celebrating National Flag Day. Confirm your attendance and be part of the ceremony honoring our flag and national unity.">

    <title>National Flag Day {{ date('Y') }} — RSVP | Republic of Liberia</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/site.js'])
</head>
<body class="flex min-h-screen flex-col bg-white font-sans text-slate-900 antialiased">

    {{-- Skip link for accessibility --}}
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-4 focus:rounded-md focus:bg-blue-950 focus:px-4 focus:py-2 focus:text-white">
        Skip to content
    </a>

    <x-site-header />

    <main id="main-content" class="flex flex-1 flex-col">
        {{-- ============ Hero ============ --}}
        <section class="relative flex flex-1 flex-col justify-center overflow-hidden bg-gradient-to-b from-slate-50 to-white">
            {{-- Watermark seal --}}
            <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                <x-seal class="h-[32rem] w-[32rem] opacity-[0.04]" />
            </div>

            <div class="relative mx-auto max-w-5xl px-6 pt-10 pb-6 text-center sm:pt-12 lg:px-8">
                <p class="animate-fade-in-up inline-flex items-center gap-2 rounded-full border border-red-100 bg-red-50 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-red-700">
                    179th National &middot; Flag Day Celebration
                </p>

                <h1 class="animate-fade-in-up mt-6 text-5xl font-extrabold uppercase tracking-tight [animation-delay:100ms] sm:text-6xl lg:text-7xl">
                    <span class="text-red-700">“From Heritage to Hope:</span>
                    <span class="block text-blue-950 sm:inline">The Flag That Unites Us.”</span>
                </h1>

                <p class="animate-fade-in-up mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-slate-600 [animation-delay:200ms]">
                    Join officials, dignitaries, and citizens from across the country as we raise our colors and
                    honor the flag that unites us — <span class="font-semibold text-slate-900">one people, one nation, indivisible</span>.
                </p>

                <div class="animate-fade-in-up mt-10 flex flex-col items-center justify-center gap-4 [animation-delay:300ms] sm:flex-row">
                    <a href="{{ route('rsvp.create') }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-red-700 px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-red-700/20 transition hover:bg-red-800 active:scale-[0.98] sm:w-auto">
                        RSVP Now
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    <a href="{{ route('flag-day.show') }}"
                       class="inline-flex w-full items-center justify-center rounded-md border border-slate-300 bg-white px-8 py-3.5 text-base font-semibold text-slate-800 transition hover:border-slate-400 hover:bg-slate-50 sm:w-auto">
                        The 179th Flag Day
                    </a>
                </div>
            </div>

            {{-- Event detail cards --}}
            <div class="relative mx-auto max-w-5xl px-6 pb-10 sm:pb-14 lg:px-8">
                <div class="animate-fade-in-up grid gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4 [animation-delay:400ms]">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm transition hover:shadow-md">
                        <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-red-50 text-red-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <rect x="3" y="4.5" width="18" height="16" rx="2" />
                                <path d="M3 9.5h18M8 3v3M16 3v3" />
                            </svg>
                        </div>
                        <h3 class="mt-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Date</h3>
                        <p class="mt-1 text-base font-bold text-blue-950">{{ date('l, F j, Y', strtotime(config('event.date'))) }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm transition hover:shadow-md">
                        <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-red-50 text-red-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path d="M12 21s-7-6.1-7-11.5A7 7 0 0119 9.5C19 14.9 12 21 12 21z" />
                                <circle cx="12" cy="9.5" r="2.25" />
                            </svg>
                        </div>
                        <h3 class="mt-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Venue</h3>
                        <p class="mt-1 text-base font-bold text-blue-950">{{ config('event.venue') }}</p>
                        <p class="text-sm text-slate-500">{{ config('event.venue_address') }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm transition hover:shadow-md">
                        <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-red-50 text-red-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <circle cx="12" cy="12" r="8.5" />
                                <path d="M12 7.5V12l3 2" />
                            </svg>
                        </div>
                        <h3 class="mt-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Time</h3>
                        <p class="mt-1 text-base font-bold text-blue-950">{{ date('g:i A', strtotime(config('event.start_time'))) }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm transition hover:shadow-md">
                        <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-red-50 text-red-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" d="M6 21V4" />
                                <path stroke-linejoin="round" d="M6 4l12 3.5L6 11" />
                            </svg>
                        </div>
                        <h3 class="mt-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Parade / Outdoor Program</h3>
                        <p class="mt-1 text-base font-bold text-blue-950">Barclay Training Center (BTC)</p>
                        <p class="text-sm text-slate-500">UN Drive, Monrovia &middot; 6:00 AM</p>
                    </div>
                </div>
            </div>
        </section>

    </main>

    {{-- ============ Footer ============ --}}
    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
            <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
                <a href="{{ route('landing') }}" class="flex items-center gap-3">
                    <x-seal class="h-9 w-9 shrink-0" />
                    <span class="leading-tight">
                        <span class="block text-[11px] font-semibold uppercase tracking-wider text-red-700">Republic of Liberia</span>
                        <span class="block text-sm font-bold text-blue-950">Ministry of Education</span>
                    </span>
                </a>

                <nav class="flex flex-wrap items-center justify-center gap-6" aria-label="Footer">
                    <a href="{{ route('flag-day.show') }}#about" class="text-sm text-slate-500 transition hover:text-blue-950">About</a>
                    <a href="{{ route('flag-day.show') }}#event-details" class="text-sm text-slate-500 transition hover:text-blue-950">Event Details</a>
                    <a href="{{ route('flag-day.show') }}#why-we-celebrate" class="text-sm text-slate-500 transition hover:text-blue-950">Why We Celebrate</a>
                    <a href="{{ route('flag-day.show') }}" class="text-sm text-slate-500 transition hover:text-blue-950">The 179th Flag Day</a>
                </nav>
            </div>

            <div class="mt-8 border-t border-slate-100 pt-8 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} Government of the Republic of Liberia, Ministry of Education. All rights reserved.
            </div>
        </div>
    </footer>

</body>
</html>
