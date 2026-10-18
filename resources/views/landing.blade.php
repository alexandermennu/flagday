{{--
    The Digital Graduation Program landing page — the single page the event QR code points
    to. Everything a guest needs (all three events, their details, and the Honors Ball RSVP)
    is reachable from here in one or two taps.
--}}
@extends('layouts.public')

@php
    $institution = config('graduation.institution');
    $classYear = config('graduation.class_year');
    $events = \App\Support\Graduation::events();
@endphp

@section('content')
    {{-- ============ Hero ============ --}}
    <section class="relative isolate overflow-hidden bg-ink text-white">
        <div class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute left-1/2 top-0 h-[42rem] w-[42rem] -translate-x-1/2 -translate-y-1/3 rounded-full bg-gold-500/20 blur-3xl"></div>
            <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.06)_1px,transparent_1px)] [background-size:22px_22px] [mask-image:radial-gradient(ellipse_at_center,black_30%,transparent_75%)]"></div>
        </div>

        <div class="mx-auto max-w-5xl px-5 pb-16 pt-14 text-center sm:pb-24 sm:pt-20 lg:px-8">
            <x-seal class="animate-fade-in-up mx-auto h-20 w-20 sm:h-24 sm:w-24" />

            <p class="animate-fade-in-up mt-6 inline-flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.3em] text-gold-400 [animation-delay:80ms]">
                <span class="h-px w-8 bg-gold-500/70"></span>
                Digital Graduation Program
                <span class="h-px w-8 bg-gold-500/70"></span>
            </p>

            <h1 class="animate-fade-in-up mt-5 [animation-delay:160ms]">
                <span class="block font-display text-2xl font-semibold tracking-wide text-white sm:text-3xl">{{ $institution }}</span>
                <span class="text-gold-gradient mt-2 block font-display text-6xl font-bold leading-none sm:text-8xl lg:text-9xl">Class of {{ $classYear }}</span>
            </h1>

            <p class="animate-fade-in-up mx-auto mt-7 max-w-xl text-base leading-relaxed text-neutral-300 sm:text-lg [animation-delay:240ms]">
                {{ config('graduation.tagline') }}
                Find everything you need for the Honors Ball, Baccalaureate, and Convocation in one place.
            </p>

            <div class="animate-fade-in-up mt-10 flex flex-col items-stretch justify-center gap-3 sm:flex-row sm:items-center [animation-delay:320ms]">
                <a href="{{ route('rsvp.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-full bg-gold-400 px-8 py-4 text-base font-semibold text-ink shadow-lg shadow-gold-500/25 transition hover:bg-gold-300 active:scale-[0.98]">
                    RSVP for the Honors Ball
                </a>
                <a href="#events"
                   class="inline-flex items-center justify-center rounded-full border border-white/25 px-8 py-4 text-base font-semibold text-white transition hover:border-gold-400 hover:text-gold-300">
                    View Graduation Events
                </a>
            </div>

            {{-- Quick links — scanning guests usually want one specific event. --}}
            <nav class="animate-fade-in-up mx-auto mt-12 grid max-w-2xl grid-cols-3 divide-x divide-white/10 rounded-2xl border border-white/10 bg-white/[0.03] [animation-delay:400ms]" aria-label="Events">
                @foreach ($events as $slug => $event)
                    <a href="{{ route('events.show', $slug) }}" class="px-2 py-4 transition hover:bg-white/5 sm:px-4">
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.2em] text-gold-500">0{{ $loop->iteration }}</span>
                        <span class="mt-1 block font-display text-sm font-semibold text-white sm:text-lg">{{ $event['name'] }}</span>
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="h-1 bg-gradient-to-r from-transparent via-gold-500 to-transparent"></div>
    </section>

    {{-- ============ Events ============ --}}
    <section id="events" class="scroll-mt-20 bg-neutral-50 py-16 sm:py-24">
        <div class="mx-auto max-w-7xl px-5 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gold-600">Graduation Season {{ $classYear }}</p>
                <h2 class="mt-3 font-display text-4xl font-semibold text-ink sm:text-5xl">Graduation Events</h2>
                <p class="mt-4 text-neutral-600">
                    Three occasions to honor the Class of {{ $classYear }}. Details are updated here as they are confirmed, so check back before each event.
                </p>
            </div>

            <div class="mt-12 grid gap-6 lg:grid-cols-3">
                @foreach ($events as $event)
                    @include('events._card', ['event' => $event, 'index' => $loop->iteration])
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ How it works ============ --}}
    <section class="bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-5xl px-5 lg:px-8">
            <h2 class="text-center font-display text-3xl font-semibold text-ink sm:text-4xl">Your Guide to Graduation</h2>

            <ol class="mt-10 grid gap-6 sm:grid-cols-3">
                @foreach ([
                    ['title' => 'Explore the events', 'body' => 'Review the date, time, venue, and dress code for each graduation event.'],
                    ['title' => 'RSVP to the Honors Ball', 'body' => 'Confirm your attendance online. You can update your response any time before the event.'],
                    ['title' => 'Bring your digital pass', 'body' => 'Your confirmation email includes an Event Pass with a QR code for check-in at the Honors Ball.'],
                ] as $step)
                    <li class="rounded-2xl border border-neutral-200 p-6">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-ink font-display text-lg font-semibold text-gold-400">{{ $loop->iteration }}</span>
                        <h3 class="mt-4 text-base font-semibold text-ink">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-neutral-600">{{ $step['body'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ============ RSVP call to action ============ --}}
    <section class="bg-white px-5 pb-16 sm:pb-24 lg:px-8">
        <div class="relative mx-auto max-w-5xl overflow-hidden rounded-3xl bg-ink px-6 py-12 text-center text-white sm:px-12 sm:py-16">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_50%_120%,rgba(231,185,58,0.35),transparent_60%)]"></div>
            <div class="relative">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gold-400">An Evening of Distinction</p>
                <h2 class="mt-3 font-display text-3xl font-semibold sm:text-5xl">Join us at the Honors Ball</h2>
                <p class="mx-auto mt-4 max-w-lg text-neutral-300">Reserve your place and receive your digital Event Pass by email.</p>
                <a href="{{ route('rsvp.create') }}"
                   class="mt-8 inline-flex items-center justify-center rounded-full bg-gold-400 px-8 py-4 text-base font-semibold text-ink transition hover:bg-gold-300 active:scale-[0.98]">
                    RSVP Now
                </a>
            </div>
        </div>
    </section>
@endsection
