<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Join the Government of the Republic of Liberia in celebrating National Flag Day. Confirm your attendance and be part of the ceremony honoring our flag and national unity.">

    <title>National Flag Day {{ date('Y') }} — RSVP | Republic of Liberia</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white font-sans text-slate-900 antialiased">

    {{-- Skip link for accessibility --}}
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-4 focus:rounded-md focus:bg-blue-950 focus:px-4 focus:py-2 focus:text-white">
        Skip to content
    </a>

    {{-- ============ Header ============ --}}
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-3 lg:px-8">
            <a href="{{ route('landing') }}" class="flex items-center gap-3">
                <x-seal class="h-10 w-10 shrink-0" />
                <span class="leading-tight">
                    <span class="block text-[11px] font-semibold uppercase tracking-wider text-red-700">Republic of Liberia</span>
                    <span class="block text-sm font-bold text-blue-950">Ministry of Education</span>
                </span>
            </a>

            <nav class="hidden items-center gap-8 md:flex" aria-label="Primary">
                <a href="#about" class="text-sm font-medium text-slate-600 transition hover:text-blue-950">About</a>
                <a href="#event-details" class="text-sm font-medium text-slate-600 transition hover:text-blue-950">Event Details</a>
                <a href="#why-we-celebrate" class="text-sm font-medium text-slate-600 transition hover:text-blue-950">Why We Celebrate</a>
            </nav>

            <a href="{{ route('rsvp.create') }}"
               class="inline-flex items-center gap-2 rounded-md bg-red-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-800 active:scale-[0.98]">
                RSVP Now
            </a>
        </div>
    </header>

    <main id="main-content">
        {{-- ============ Hero ============ --}}
        <section class="relative overflow-hidden bg-gradient-to-b from-slate-50 to-white">
            {{-- Watermark seal --}}
            <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                <x-seal class="h-[32rem] w-[32rem] opacity-[0.04]" />
            </div>

            <div class="relative mx-auto max-w-5xl px-6 pt-20 pb-16 text-center sm:pt-28 lg:px-8">
                <p class="animate-fade-in-up inline-flex items-center gap-2 rounded-full border border-red-100 bg-red-50 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-red-700">
                    Republic of Liberia &middot; Ministry of Education
                </p>

                <h1 class="animate-fade-in-up mt-6 text-5xl font-extrabold uppercase tracking-tight [animation-delay:100ms] sm:text-6xl lg:text-7xl">
                    <span class="text-red-700">One Nation,</span>
                    <span class="block text-blue-950 sm:inline">Indivisible</span>
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
                    <a href="#about"
                       class="inline-flex w-full items-center justify-center rounded-md border border-slate-300 bg-white px-8 py-3.5 text-base font-semibold text-slate-800 transition hover:border-slate-400 hover:bg-slate-50 sm:w-auto">
                        Learn More
                    </a>
                </div>
            </div>

            {{-- Flag visual --}}
            <div class="relative flex justify-center pb-16 sm:pb-24" style="perspective: 800px;">
                <div class="flex flex-col items-center">
                    <div class="animate-flag-wave drop-shadow-md">
                        <x-liberian-flag class="h-auto w-48 sm:w-64" />
                    </div>
                    <div class="h-40 w-1.5 rounded-b-sm bg-gradient-to-b from-slate-300 to-slate-400 sm:h-56"></div>
                    <div class="h-2 w-16 rounded-full bg-slate-300 sm:w-20"></div>
                </div>
            </div>
        </section>

        {{-- ============ About the Event ============ --}}
        <section id="about" class="mx-auto max-w-7xl px-6 py-20 sm:py-28 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
                <div>
                    <span class="text-sm font-semibold uppercase tracking-wider text-red-700">About the Event</span>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-blue-950 sm:text-4xl">
                        A National Tradition of Unity
                    </h2>
                    <p class="mt-6 leading-relaxed text-slate-600">
                        <span class="font-semibold text-slate-900">National Flag Day</span> commemorates the official
                        adoption of the flag of the Republic of Liberia and is observed annually as a public holiday
                        across the country. <span class="font-semibold text-slate-900">Hosted by the Government of
                        the Republic of Liberia</span>, this year's ceremony brings together government officials,
                        traditional leaders, school communities, and members of the public for a formal flag-raising,
                        remarks from national leadership, and cultural presentations.
                    </p>
                    <p class="mt-4 leading-relaxed text-slate-600">
                        This RSVP platform allows invited guests to confirm their attendance ahead of the ceremony,
                        helping organizers plan seating, security, and hospitality for a smooth and dignified event.
                    </p>
                </div>

                <div class="relative">
                    <div class="aspect-[4/3] w-full overflow-hidden rounded-2xl bg-gradient-to-br from-blue-950 to-blue-900 shadow-xl">
                        <div class="flex h-full w-full items-center justify-center">
                            <x-liberian-flag class="w-1/2 opacity-90 drop-shadow-2xl" />
                        </div>
                    </div>
                    <div class="absolute -bottom-4 -right-4 -z-10 hidden h-full w-full rounded-2xl border-2 border-red-700/20 sm:block"></div>
                </div>
            </div>
        </section>

        {{-- ============ Event Details ============ --}}
        {{-- Placeholder logistics below — update once the venue and program schedule are finalized --}}
        <section id="event-details" class="bg-slate-50 py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <span class="text-sm font-semibold uppercase tracking-wider text-red-700">Event Details</span>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-blue-950 sm:text-4xl">
                        Where and When to Join Us
                    </h2>
                </div>

                <div class="mx-auto mt-14 grid max-w-5xl gap-6 sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm transition hover:shadow-md">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-700">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <rect x="3" y="4.5" width="18" height="16" rx="2" />
                                <path d="M3 9.5h18M8 3v3M16 3v3" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-sm font-semibold uppercase tracking-wider text-slate-500">Date</h3>
                        <p class="mt-1 text-lg font-bold text-blue-950">{{ date('l, F j', strtotime(config('event.date'))) }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm transition hover:shadow-md">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-700">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <circle cx="12" cy="12" r="8.5" />
                                <path d="M12 7.5V12l3 2" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-sm font-semibold uppercase tracking-wider text-slate-500">Time</h3>
                        <p class="mt-1 text-lg font-bold text-blue-950">{{ date('g:i A', strtotime(config('event.start_time'))) }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm transition hover:shadow-md">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-700">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path d="M12 21s-7-6.1-7-11.5A7 7 0 0119 9.5C19 14.9 12 21 12 21z" />
                                <circle cx="12" cy="9.5" r="2.25" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-sm font-semibold uppercase tracking-wider text-slate-500">Venue</h3>
                        <p class="mt-1 text-lg font-bold text-blue-950">{{ config('event.venue') }}</p>
                        <p class="text-sm text-slate-500">{{ config('event.venue_address') }}</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============ Why We Celebrate ============ --}}
        <section id="why-we-celebrate" class="mx-auto max-w-7xl px-6 py-20 sm:py-28 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <span class="text-sm font-semibold uppercase tracking-wider text-red-700">Why We Celebrate</span>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-blue-950 sm:text-4xl">
                    What Our Flag Represents
                </h2>
                <p class="mt-4 leading-relaxed text-slate-600">
                    Every element of the Liberian flag carries meaning — a reminder of where we come from and the
                    values that hold us together as one nation.
                </p>
            </div>

            <div class="mx-auto mt-14 grid max-w-5xl gap-6 sm:grid-cols-3">
                <div class="rounded-2xl bg-slate-50 p-8">
                    <div class="text-3xl font-extrabold text-red-700">11</div>
                    <h3 class="mt-2 font-semibold text-blue-950">Eleven Stripes</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Representing the eleven signatories of Liberia's Declaration of Independence.
                    </p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-8">
                    <div class="text-3xl font-extrabold text-red-700">1</div>
                    <h3 class="mt-2 font-semibold text-blue-950">The Lone Star</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Symbolizing Liberia's status as the first independent republic in Africa.
                    </p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-8">
                    <div class="text-3xl font-extrabold text-red-700">3</div>
                    <h3 class="mt-2 font-semibold text-blue-950">Red, White &amp; Blue</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Colors representing valor, purity, and liberty — the founding ideals of our republic.
                    </p>
                </div>
            </div>
        </section>

        {{-- ============ RSVP Banner ============ --}}
        <section class="relative overflow-hidden bg-blue-950">
            <div class="pointer-events-none absolute inset-0 flex items-center justify-end pr-8 opacity-10">
                <x-seal class="h-64 w-64" />
            </div>
            <div class="relative mx-auto max-w-4xl px-6 py-16 text-center lg:px-8">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    Have you confirmed your attendance?
                </h2>
                <p class="mx-auto mt-4 max-w-xl text-blue-100">
                    Secure your place at this year's National Flag Day ceremony in just a few minutes.
                </p>
                <a href="{{ route('rsvp.create') }}"
                   class="mt-8 inline-flex items-center justify-center gap-2 rounded-md bg-red-700 px-8 py-3.5 text-base font-semibold text-white shadow-lg transition hover:bg-red-600 active:scale-[0.98]">
                    RSVP Now
                </a>
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

                <nav class="flex items-center gap-6" aria-label="Footer">
                    <a href="#about" class="text-sm text-slate-500 transition hover:text-blue-950">About</a>
                    <a href="#event-details" class="text-sm text-slate-500 transition hover:text-blue-950">Event Details</a>
                    <a href="#why-we-celebrate" class="text-sm text-slate-500 transition hover:text-blue-950">Why We Celebrate</a>
                </nav>
            </div>

            <div class="mt-8 border-t border-slate-100 pt-8 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} Government of the Republic of Liberia, Ministry of Education. All rights reserved.
            </div>
        </div>
    </footer>

</body>
</html>
