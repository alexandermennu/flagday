<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Everything you need to know about the 179th National Flag Day Celebration — programme, venue, participating schools, and how to RSVP.">

    <title>{{ \Illuminate\Support\Number::ordinal(date('Y', strtotime(config('event.date'))) - 1847) }} National Flag Day Celebration | Ministry of Education, Republic of Liberia</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/site.js'])
</head>
<body class="bg-white font-sans text-slate-900 antialiased">

    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-4 focus:rounded-md focus:bg-blue-950 focus:px-4 focus:py-2 focus:text-white">
        Skip to content
    </a>

    @php
        $edition = \Illuminate\Support\Number::ordinal(date('Y', strtotime(config('event.date'))) - 1847);
    @endphp

    <x-site-header />

    <main id="main-content">
        {{-- ============ Hero ============ --}}
        <section class="relative overflow-hidden bg-gradient-to-br from-blue-950 via-blue-950 to-slate-900">
            <div class="pointer-events-none absolute inset-0 flex items-center justify-center opacity-[0.06]">
                <x-seal class="h-[36rem] w-[36rem]" />
            </div>

            <div class="relative mx-auto max-w-5xl px-6 py-24 sm:py-32 lg:px-8">
                <p class="animate-fade-in-up text-center text-xs font-semibold uppercase tracking-[0.2em] text-red-400">
                    Celebrating Our Unity. Honoring Our Heritage.
                </p>

                <h1 class="animate-fade-in-up mt-4 text-center text-4xl font-extrabold uppercase tracking-tight text-white [animation-delay:100ms] sm:text-5xl lg:text-6xl">
                    The {{ $edition }} National <br class="hidden sm:block">Flag Day
                </h1>

                <p class="animate-fade-in-up mx-auto mt-6 max-w-xl text-center text-lg italic leading-relaxed text-blue-100 [animation-delay:150ms]">
                    &ldquo;{{ config('event.theme') }}&rdquo;
                </p>

                <div class="animate-fade-in-up mt-12 flex flex-col items-center justify-center gap-4 [animation-delay:250ms] sm:flex-row">
                    <a href="{{ route('rsvp.create') }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-red-700 px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-red-950/30 transition hover:bg-red-600 active:scale-[0.98] sm:w-auto">
                        RSVP Now
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    <button type="button" title="Coming soon"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-white/25 bg-white/5 px-8 py-3.5 text-base font-semibold text-white transition hover:bg-white/10 sm:w-auto">
                        Download Programme
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v11m0 0l-4-4m4 4l4-4M5 19h14" />
                        </svg>
                    </button>
                </div>
            </div>
        </section>

        {{-- ============ Quick Facts Strip ============ --}}
        <section id="event-details" class="border-b border-slate-200 bg-white">
            <div class="mx-auto grid max-w-7xl grid-cols-2 gap-6 px-6 py-8 sm:grid-cols-4 lg:px-8">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><rect x="3" y="4.5" width="18" height="16" rx="2" /><path d="M3 9.5h18M8 3v3M16 3v3" /></svg>
                    </span>
                    <span>
                        <span class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400">Date</span>
                        <span class="block text-sm font-bold text-blue-950">{{ date('F j, Y', strtotime(config('event.date'))) }}</span>
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0119 9.5C19 14.9 12 21 12 21z" /><circle cx="12" cy="9.5" r="2.25" /></svg>
                    </span>
                    <span>
                        <span class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400">Venue</span>
                        <span class="block text-sm font-bold text-blue-950">{{ config('event.venue') }}</span>
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><circle cx="12" cy="12" r="8.5" /><path d="M12 7.5V12l3 2" /></svg>
                    </span>
                    <span>
                        <span class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400">Time</span>
                        <span class="block text-sm font-bold text-blue-950">{{ date('g:i A', strtotime(config('event.start_time'))) }}</span>
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M9 6a3 3 0 116 0c0 1.5-1.5 2-1.5 3.5" /><path d="M6 21v-4a6 6 0 1112 0v4" /></svg>
                    </span>
                    <span>
                        <span class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400">Dress Code</span>
                        <span class="block text-sm font-bold text-blue-950">{{ config('event.dress_code') }}</span>
                    </span>
                </div>
            </div>
        </section>

        {{-- ============ About ============ --}}
        <section id="about" class="mx-auto max-w-7xl px-6 py-20 sm:py-28 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
                <div>
                    <span class="text-sm font-semibold uppercase tracking-wider text-red-700">About the Celebration</span>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-blue-950 sm:text-4xl">
                        A Day That Brings Us Together
                    </h2>
                    <p class="mt-6 leading-relaxed text-slate-600">
                        National Flag Day is a time to honor our history, celebrate our unity, and renew our
                        commitment to the ideals of liberty, justice, and opportunity for all.
                    </p>
                    <p class="mt-4 leading-relaxed text-slate-600">
                        The {{ $edition }} celebration under the theme &ldquo;{{ config('event.theme') }}&rdquo; reaffirms
                        our shared responsibility to build a stronger, brighter Liberia — bringing together government
                        officials, traditional leaders, school communities, and members of the public for a formal
                        flag-raising, remarks from national leadership, and cultural presentations.
                    </p>
                </div>

                <div class="relative flex items-center justify-center" style="perspective: 1200px;">
                    <div class="animate-flag-wave drop-shadow-2xl">
                        <x-liberian-flag class="h-auto w-64 sm:w-80 lg:w-[27rem]" />
                    </div>
                </div>
            </div>
        </section>

        {{-- ============ Why We Celebrate ============ --}}
        <section id="why-we-celebrate" class="bg-slate-50 py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
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
                    <div class="rounded-2xl bg-white p-8 shadow-sm">
                        <div class="text-3xl font-extrabold text-red-700">11</div>
                        <h3 class="mt-2 font-semibold text-blue-950">Eleven Stripes</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">
                            Representing the eleven signatories of Liberia's Declaration of Independence.
                        </p>
                    </div>
                    <div class="rounded-2xl bg-white p-8 shadow-sm">
                        <div class="text-3xl font-extrabold text-red-700">1</div>
                        <h3 class="mt-2 font-semibold text-blue-950">The Lone Star</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">
                            Symbolizing Liberia's status as the first independent republic in Africa.
                        </p>
                    </div>
                    <div class="rounded-2xl bg-white p-8 shadow-sm">
                        <div class="text-3xl font-extrabold text-red-700">3</div>
                        <h3 class="mt-2 font-semibold text-blue-950">Red, White &amp; Blue</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">
                            Colors representing valor, purity, and liberty — the founding ideals of our republic.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============ Leadership ============ --}}
        <section class="bg-slate-50 py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">

                {{-- President's Special Message — full-width feature card --}}
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg shadow-slate-200/60">
                    <div class="grid lg:grid-cols-[22rem_1fr]">
                        <div class="h-72 lg:h-auto">
                            <img src="{{ asset('images/people/president-boakai.png') }}"
                                 alt="H.E. Joseph Nyuma Boakai, Sr., President of the Republic of Liberia"
                                 class="h-full w-full object-cover object-top">
                        </div>
                        <div class="p-8 sm:p-10 lg:p-12">
                            <span class="inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-red-700">
                                President's Special Message
                            </span>
                            <p class="mt-6 text-xl leading-relaxed font-medium text-slate-800 sm:text-2xl">
                                &ldquo;On this special day, let us remember the sacrifices of our forebears and the
                                power of unity that continues to move our nation forward. Together, let us build a
                                Liberia where every child can dream, learn, and achieve.&rdquo;
                            </p>
                            <p class="mt-6 text-base font-bold text-blue-950">H.E. Joseph Nyuma Boakai, Sr.</p>
                            <p class="text-sm text-slate-500">President of the Republic of Liberia</p>

                            <details class="group mt-6">
                                <summary class="inline-flex cursor-pointer list-none items-center gap-1.5 rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 [&::-webkit-details-marker]:hidden">
                                    Read Full Message
                                    <svg class="h-3.5 w-3.5 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                                </summary>
                                <p class="mt-4 leading-relaxed text-slate-600">
                                    Fellow citizens, as we gather once more to raise our beloved flag, we are reminded that
                                    the eleven stripes and lone star are not merely symbols of cloth and thread — they are a
                                    covenant between generations past, present, and future. Let this {{ $edition }} celebration
                                    renew our resolve to serve one another and to leave a stronger Liberia for those who follow.
                                </p>
                            </details>
                        </div>
                    </div>
                </div>

                {{-- Minister of Education & National Orator — equal-width, subordinate cards --}}
                <div class="mt-6 grid gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                        <span class="text-xs font-semibold uppercase tracking-wider text-red-700">Welcome from the Minister of Education</span>
                        <div class="mt-5 flex items-start gap-4">
                            <img src="{{ asset('images/people/jarso-jallah.jpeg') }}"
                                 alt="Dr. Jarso Maley Jallah, Minister of Education"
                                 class="h-16 w-16 shrink-0 rounded-full object-cover object-top ring-2 ring-slate-100">
                            <div>
                                <p class="leading-relaxed text-slate-600">
                                    &ldquo;It is my honor to welcome you to this year's National Flag Day celebration.
                                    Our schools and communities have worked together to make this ceremony a true
                                    reflection of the unity our flag represents.&rdquo;
                                </p>
                                <p class="mt-4 text-sm font-bold text-blue-950">Dr. Jarso Maley Jallah</p>
                                <p class="text-sm text-slate-500">Minister of Education, Republic of Liberia</p>
                            </div>
                        </div>
                        <details class="group mt-5">
                            <summary class="inline-flex cursor-pointer list-none items-center gap-1.5 rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 [&::-webkit-details-marker]:hidden">
                                Read More
                                <svg class="h-3.5 w-3.5 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                            </summary>
                            <p class="mt-4 leading-relaxed text-slate-600">
                                The Ministry of Education is proud to coordinate this year's participating schools,
                                bringing students from across the country to take part in the flag-raising ceremony
                                and cultural programme. This celebration is as much theirs as it is the nation's.
                            </p>
                        </details>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                        <span class="text-xs font-semibold uppercase tracking-wider text-red-700">National Orator</span>
                        <div class="mt-5 flex items-start gap-4">
                            <img src="{{ asset('images/people/augustine-ngafuan.jpeg') }}"
                                 alt="Hon. Augustine Kpehe Ngafuan"
                                 class="h-16 w-16 shrink-0 rounded-full object-cover object-top ring-2 ring-slate-100">
                            <div>
                                <p class="text-sm font-bold text-blue-950">Hon. Augustine Kpehe Ngafuan</p>
                                <p class="text-sm text-slate-500">Minister of Finance &amp; Development Planning</p>
                                <p class="text-sm text-slate-500">National Orator, {{ $edition }} National Flag Day Celebration</p>
                                <p class="mt-4 leading-relaxed text-slate-600">
                                    A veteran public servant and economist, Minister Ngafuan brings decades of
                                    leadership in fiscal policy and national development planning to this year's
                                    orator address.
                                </p>
                            </div>
                        </div>
                        <details class="group mt-5">
                            <summary class="inline-flex cursor-pointer list-none items-center gap-1.5 rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 [&::-webkit-details-marker]:hidden">
                                Read Profile
                                <svg class="h-3.5 w-3.5 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                            </summary>
                            <p class="mt-4 leading-relaxed text-slate-600">
                                Minister Ngafuan has held senior posts across Liberia's government, including Foreign
                                Minister and Finance Minister. This year's orator address will center on the role of
                                sound stewardship and national unity in building a stronger Liberia.
                            </p>
                        </details>
                    </div>
                </div>

            </div>
        </section>

        {{-- ============ Event Theme ============ --}}
        <section class="relative overflow-hidden bg-blue-950">
            <div class="pointer-events-none absolute inset-0 flex items-center justify-end pr-8 opacity-10">
                <x-seal class="h-64 w-64" />
            </div>
            <div class="relative mx-auto flex max-w-5xl flex-col items-center gap-6 px-6 py-16 text-center lg:px-8">
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-white/10">
                    <x-liberian-flag class="h-6 w-auto" />
                </span>
                <span class="text-xs font-semibold uppercase tracking-wider text-red-400">This Year's Theme</span>
                <h2 class="text-2xl font-bold italic tracking-tight text-white sm:text-3xl">
                    &ldquo;{{ config('event.theme') }}&rdquo;
                </h2>
                <p class="max-w-2xl text-blue-100">
                    This theme reflects our journey as a people — rooted in a rich heritage, guided by hope, and
                    united under the flag of Liberia as we work together for a brighter future.
                </p>
            </div>
        </section>

        {{-- ============ Programme / Schools / Venue ============ --}}
        <section id="programme" class="mx-auto max-w-7xl px-6 py-20 sm:py-28 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-3 lg:gap-8">
                {{-- Programme of Events --}}
                <div>
                    <h3 class="border-l-4 border-red-700 pl-3 text-lg font-bold text-blue-950">Programme of Events</h3>
                    <ol class="mt-6 space-y-5 border-l-2 border-slate-200 pl-5">
                        @foreach ($programme as $item)
                            <li class="relative">
                                <span class="absolute -left-[27px] top-1 h-2.5 w-2.5 rounded-full border-2 border-red-700 bg-white"></span>
                                <span class="block text-xs font-semibold uppercase tracking-wider text-red-700">{{ $item['time'] }}</span>
                                <span class="block text-sm font-medium text-slate-700">{{ $item['title'] }}</span>
                            </li>
                        @endforeach
                    </ol>
                    <button type="button" title="Coming soon"
                            class="mt-6 inline-flex items-center gap-2 rounded-md border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Download Full Programme
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v11m0 0l-4-4m4 4l4-4M5 19h14" /></svg>
                    </button>
                </div>

                {{-- Official Schools Parade Line-Up --}}
                <div id="schools">
                    <h3 class="border-l-4 border-red-700 pl-3 text-lg font-bold text-blue-950">Official Schools Parade Line-Up</h3>

                    <div class="mt-6 relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="M21 21l-4.35-4.35" /></svg>
                        <input type="text" id="school-search" placeholder="Search schools&hellip;"
                               class="w-full rounded-md border border-slate-300 py-2.5 pl-9 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                    </div>

                    <div class="mt-5 overflow-x-auto">
                        <table class="w-full min-w-[560px] border-collapse text-sm">
                            <thead>
                                <tr class="bg-blue-950 text-left text-white">
                                    <th class="px-3 py-2.5 font-semibold">Position</th>
                                    <th class="px-3 py-2.5 font-semibold">Name of School</th>
                                    <th class="px-3 py-2.5 font-semibold">Last Year's Rank</th>
                                    <th class="px-3 py-2.5 font-semibold">Contact Number</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($schools as $school)
                                    <tr data-school-row
                                        data-school-name="{{ strtolower($school['name']) }}"
                                        class="border-b border-slate-200 last:border-0 hover:bg-slate-50">
                                        <td class="px-3 py-2.5 text-slate-500">{{ $school['position'] }}</td>
                                        <td class="px-3 py-2.5 font-medium text-slate-700">{{ $school['name'] }}</td>
                                        <td class="px-3 py-2.5 text-slate-600">{{ $school['rank'] }}</td>
                                        <td class="px-3 py-2.5 text-slate-500">{{ $school['contact'] }}</td>
                                    </tr>
                                @endforeach
                                <tr id="school-empty-state" class="hidden">
                                    <td colspan="4" class="px-3 py-4 text-center text-sm text-slate-400">No schools match your search.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Venue Information --}}
                <div id="venue">
                    <h3 class="border-l-4 border-red-700 pl-3 text-lg font-bold text-blue-950">Venue Information</h3>
                    <x-image-placeholder class="mt-6 aspect-[4/3] w-full" label="Venue photo placeholder" />
                    <div class="mt-4 flex items-start gap-2.5">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0119 9.5C19 14.9 12 21 12 21z" /><circle cx="12" cy="9.5" r="2.25" /></svg>
                        <span class="text-sm">
                            <span class="block font-bold text-blue-950">{{ config('event.venue') }}</span>
                            <span class="text-slate-500">{{ config('event.venue_address') }}</span>
                        </span>
                    </div>
                    <button type="button" title="Coming soon"
                            class="mt-4 inline-flex items-center gap-2 rounded-md border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        View on Map
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l-6 3v11l6-3m0-11l6 3m-6-3v11m6-8l6-3v11l-6 3m0-11v11" /></svg>
                    </button>

                    <ul class="mt-6 space-y-3 text-sm text-slate-600">
                        <li class="flex items-center gap-2.5">
                            <svg class="h-4 w-4 shrink-0 text-red-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><rect x="3" y="7" width="15" height="11" rx="1.5" /><path d="M18 10.5l3-1.75v6.5L18 13.5" /></svg>
                            Ample parking available
                        </li>
                        <li class="flex items-center gap-2.5">
                            <svg class="h-4 w-4 shrink-0 text-red-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M12 3l7 3v5.5c0 4.5-3 8-7 9.5-4-1.5-7-5-7-9.5V6l7-3z" /></svg>
                            Security will be enforced
                        </li>
                        <li class="flex items-center gap-2.5">
                            <svg class="h-4 w-4 shrink-0 text-red-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0119 9.5C19 14.9 12 21 12 21z" /><circle cx="12" cy="9.5" r="2.25" /></svg>
                            Follow signs for event directions
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        {{-- ============ Photo Gallery ============ --}}
        <section id="gallery" class="bg-slate-50 py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="text-center sm:text-left">
                        <span class="text-sm font-semibold uppercase tracking-wider text-red-700">Previous Celebrations</span>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-blue-950 sm:text-3xl">Photo Gallery</h2>
                    </div>
                    <button type="button" title="Coming soon"
                            class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        View Gallery
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                </div>

                <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                    @for ($i = 0; $i < 6; $i++)
                        <x-image-placeholder class="aspect-square w-full" :label="null" />
                    @endfor
                </div>
            </div>
        </section>

        {{-- ============ FAQ ============ --}}
        <section id="faq" class="mx-auto max-w-4xl px-6 py-20 sm:py-28 lg:px-8">
            <div class="text-center">
                <span class="text-sm font-semibold uppercase tracking-wider text-red-700">Frequently Asked Questions</span>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-blue-950 sm:text-4xl">Good to Know</h2>
            </div>

            <div class="mt-12 space-y-3">
                @foreach ($faqs as $faq)
                    <details class="group rounded-xl border border-slate-200 bg-white px-5 py-4 open:shadow-sm">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-semibold text-blue-950 [&::-webkit-details-marker]:hidden">
                            {{ $faq['question'] }}
                            <svg class="h-4 w-4 shrink-0 text-red-700 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                        </summary>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $faq['answer'] }}</p>
                    </details>
                @endforeach
            </div>
        </section>

        {{-- ============ Partners ============ --}}
        <section id="partners" class="border-y border-slate-200 bg-slate-50 py-16">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <p class="text-center text-sm font-semibold uppercase tracking-wider text-slate-400">Our Partners</p>
                <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    @foreach ($partners as $partner)
                        <div class="flex h-16 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-center text-xs font-semibold uppercase tracking-wide text-slate-400">
                            {{ $partner }}
                        </div>
                    @endforeach
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
    <footer id="contact" class="bg-blue-950 text-blue-100">
        <div class="mx-auto max-w-7xl px-6 py-16 lg:px-8">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-5 lg:gap-8">
                <div class="lg:col-span-2">
                    <a href="{{ route('landing') }}" class="flex items-center gap-3">
                        <x-seal class="h-10 w-10 shrink-0" />
                        <span class="leading-tight">
                            <span class="block text-sm font-bold text-white">Ministry of Education</span>
                            <span class="block text-xs text-blue-300">Republic of Liberia</span>
                        </span>
                    </a>
                    <p class="mt-4 max-w-xs text-sm leading-relaxed text-blue-200">
                        Show the Light, The People Will Find the Way.
                    </p>
                    <div class="mt-5 flex items-center gap-3">
                        @foreach (['facebook', 'twitter', 'youtube', 'instagram'] as $network)
                            <button type="button" title="Coming soon"
                                    class="flex h-9 w-9 items-center justify-center rounded-full border border-white/20 text-blue-100 transition hover:bg-white/10">
                                <span class="sr-only">{{ ucfirst($network) }}</span>
                                @switch($network)
                                    @case('facebook')
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-7.5H16l.5-3H13.5V8.5c0-.9.25-1.5 1.55-1.5H16.5V4.35c-.28-.04-1.24-.1-2.36-.1-2.34 0-3.94 1.43-3.94 4.04V10.5H8v3h2.2V21h3.3z" /></svg>
                                        @break
                                    @case('twitter')
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 5.9c-.7.3-1.5.5-2.3.6.8-.5 1.5-1.3 1.8-2.3-.8.5-1.7.8-2.6 1a4.1 4.1 0 00-7 3.7A11.6 11.6 0 013 4.9a4.1 4.1 0 001.3 5.5c-.6 0-1.3-.2-1.8-.5v.1c0 2 1.4 3.6 3.3 4a4.2 4.2 0 01-1.8.1 4.1 4.1 0 003.9 2.9A8.3 8.3 0 012 18.4a11.6 11.6 0 006.3 1.8c7.5 0 11.7-6.3 11.7-11.7v-.5c.8-.6 1.5-1.3 2-2.1z" /></svg>
                                        @break
                                    @case('youtube')
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.6 7.2s-.2-1.5-.8-2.1c-.8-.8-1.7-.8-2.1-.9C15.9 4 12 4 12 4h0s-3.9 0-6.7.2c-.4 0-1.3.1-2.1.9-.6.6-.8 2.1-.8 2.1S2.2 9 2.2 10.7v1.6c0 1.7.2 3.5.2 3.5s.2 1.5.8 2.1c.8.8 1.9.8 2.4.9 1.7.1 7.4.2 7.4.2s3.9 0 6.7-.2c.4 0 1.3-.1 2.1-.9.6-.6.8-2.1.8-2.1s.2-1.7.2-3.5v-1.6c0-1.7-.2-3.5-.2-3.5zM9.9 14.6V8.9l5.4 2.9-5.4 2.8z" /></svg>
                                        @break
                                    @case('instagram')
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="4.5" /><circle cx="12" cy="12" r="3.75" /><circle cx="17.25" cy="6.75" r="0.75" fill="currentColor" stroke="none" /></svg>
                                @endswitch
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-blue-300">Quick Links</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li><a href="{{ route('landing') }}" class="transition hover:text-white">Home</a></li>
                        <li><a href="#about" class="transition hover:text-white">About</a></li>
                        <li><a href="#gallery" class="transition hover:text-white">Gallery</a></li>
                        <li><a href="{{ route('rsvp.create') }}" class="transition hover:text-white">RSVP</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-blue-300">Event Details</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li>{{ date('l, F j, Y', strtotime(config('event.date'))) }}</li>
                        <li>{{ date('g:i A', strtotime(config('event.start_time'))) }}</li>
                        <li>{{ config('event.venue') }}, {{ config('event.venue_address') }}</li>
                    </ul>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-blue-300">Contact Us</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li>{{ config('event.contact_phone') }}</li>
                        <li>{{ config('event.contact_email') }}</li>
                        <li>{{ config('event.contact_website') }}</li>
                    </ul>

                    <p class="mt-6 text-xs font-semibold uppercase tracking-wider text-blue-300">Newsletter</p>
                    <form class="mt-3 flex items-center gap-2" onsubmit="return false">
                        <label for="newsletter-email" class="sr-only">Email address</label>
                        <input type="email" id="newsletter-email" placeholder="Enter your email"
                               class="w-full rounded-md border border-white/20 bg-white/5 px-3 py-2 text-sm text-white placeholder:text-blue-300 focus:border-white/40 focus:outline-none">
                        <button type="button" title="Coming soon"
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-red-700 text-white transition hover:bg-red-600">
                            <span class="sr-only">Subscribe</span>
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-12 border-t border-white/10 pt-8 text-center text-xs text-blue-300">
                &copy; {{ date('Y') }} Ministry of Education, Republic of Liberia. All rights reserved.
            </div>
        </div>
    </footer>

</body>
</html>
