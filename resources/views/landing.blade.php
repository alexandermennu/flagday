{{--
    The Digital Graduation Program landing page — the single page the event QR code points
    to. Deliberately just the hero: it fills one viewport (min-height, so it can still grow
    on small screens) and links out to the Class of 2026 and Honors Ball pages.
--}}
@extends('layouts.public')

@section('no_footer', true)

@php
    $institution = config('graduation.institution');
    $classYear = config('graduation.class_year');
    $events = \App\Support\Graduation::events();
@endphp

@section('content')
    {{-- ============ Hero ============ --}}
    {{-- 4.5rem + 1px = the sticky header's height, so header + hero together fill the screen. --}}
    <section class="relative isolate flex min-h-[calc(100svh-4.5rem-1px)] flex-1 flex-col overflow-hidden bg-ink text-white">
        <div class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute left-1/2 top-0 h-[42rem] w-[42rem] -translate-x-1/2 -translate-y-1/3 rounded-full bg-gold-500/20 blur-3xl"></div>
            <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.06)_1px,transparent_1px)] [background-size:22px_22px] [mask-image:radial-gradient(ellipse_at_center,black_30%,transparent_75%)]"></div>
        </div>
        @include('events._hero-celebration', ['layout' => 'centered'])

        <div class="mx-auto my-auto w-full max-w-5xl px-5 py-8 text-center sm:py-16 short:py-8 lg:px-8">
            <x-seal class="animate-fade-in-up mx-auto h-24 w-24 drop-shadow-[0_8px_30px_rgba(231,185,58,0.25)] sm:h-40 sm:w-40 short:h-28 short:w-28 shorter:h-24 shorter:w-24" />

            <p class="animate-fade-in-up mt-6 inline-flex items-center gap-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-gold-400 max-sm:mt-5 sm:text-xs sm:tracking-[0.3em] short:mt-4 [animation-delay:80ms]">
                <span class="h-px w-6 bg-gold-500/70 sm:w-8"></span>
                Digital Graduation Program
                <span class="h-px w-6 bg-gold-500/70 sm:w-8"></span>
            </p>

            <h1 class="animate-fade-in-up mt-5 short:mt-3 [animation-delay:160ms]">
                <span class="block font-display text-2xl font-semibold tracking-wide text-white sm:text-3xl">{{ $institution }}</span>
                <span class="text-gold-gradient mt-2 block font-display text-6xl font-bold leading-none sm:text-8xl lg:text-9xl short:lg:text-8xl">Class of {{ $classYear }}</span>
            </h1>

            <p class="animate-fade-in-up mx-auto mt-5 max-w-xl text-base sm:mt-7 leading-relaxed text-neutral-300 sm:text-lg short:mt-5 [animation-delay:240ms]">
                {{ config('graduation.tagline') }}
                Find everything you need for the Honors Ball, Baccalaureate, and Convocation in one place.
            </p>

            <div class="animate-fade-in-up mt-8 flex flex-col items-stretch justify-center gap-3 sm:mt-10 sm:flex-row sm:items-center short:mt-7 [animation-delay:320ms]">
                <a href="{{ route('rsvp.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-full bg-gold-400 px-8 py-4 text-base font-semibold text-ink shadow-lg shadow-gold-500/25 transition hover:bg-gold-300 active:scale-[0.98]">
                    RSVP for the Honors Ball
                </a>
                <a href="{{ route('class.show') }}"
                   class="inline-flex items-center justify-center rounded-full border border-white/25 px-8 py-4 text-base font-semibold text-white transition hover:border-gold-400 hover:text-gold-300">
                    View Graduation Events
                </a>
            </div>

            {{-- Event summary — Honors Ball has its own page; the others live on the Class of 2026 page. --}}
            <nav class="animate-fade-in-up mx-auto mt-8 grid max-w-2xl sm:mt-12 grid-cols-3 divide-x divide-white/10 rounded-2xl border border-white/10 bg-white/[0.03] short:mt-8 [animation-delay:400ms]" aria-label="Events">
                @foreach ($events as $slug => $event)
                    <a href="{{ $event['rsvp'] ? route('events.show', $slug) : route('class.show').'#'.$slug }}" class="px-2 py-4 transition hover:bg-white/5 sm:px-4 short:py-3">
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.2em] text-gold-500">0{{ $loop->iteration }}</span>
                        <span class="mt-1 block font-display text-sm font-semibold text-white sm:text-lg">{{ $event['name'] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="h-1 shrink-0 bg-gradient-to-r from-transparent via-gold-500 to-transparent"></div>
    </section>
@endsection
