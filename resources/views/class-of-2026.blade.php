{{--
    The "Class of 2026" page — every graduation event with its published details, plus the
    digital program booklets. Content comes from config/graduation.php via App\Support\Graduation.
--}}
@extends('layouts.public')

@php
    $institution = config('graduation.institution');
    $classYear = config('graduation.class_year');
    $events = \App\Support\Graduation::events();
@endphp

@section('title', 'Class of '.$classYear.' | '.$institution.' Digital Graduation Program')
@section('description', 'Graduation events and digital program booklets for the '.$institution.' Class of '.$classYear.' — Honors Ball, Baccalaureate, and Convocation.')

@section('content')
    <section class="relative isolate overflow-hidden bg-ink text-white">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_80%_-10%,rgba(231,185,58,0.3),transparent_55%)]"></div>
        <div class="mx-auto max-w-5xl px-5 pb-14 pt-12 sm:pb-20 sm:pt-16 lg:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gold-400">Graduation Season {{ $classYear }}</p>
            <h1 class="mt-3 font-display text-5xl font-bold sm:text-7xl">
                <span class="text-gold-gradient">Class of {{ $classYear }}</span>
            </h1>
            <p class="mt-3 text-sm font-semibold uppercase tracking-[0.2em] text-neutral-400">{{ $institution }}</p>
            <p class="mt-6 max-w-2xl text-lg leading-relaxed text-neutral-300">
                Three occasions to honor the Class of {{ $classYear }}. Details and program booklets are published here
                as they are confirmed, so check back before each event.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#events" class="inline-flex items-center justify-center rounded-full border border-white/25 px-6 py-3 text-sm font-semibold text-white transition hover:border-gold-400 hover:text-gold-300">Graduation Events</a>
                <a href="#programs" class="inline-flex items-center justify-center rounded-full border border-white/25 px-6 py-3 text-sm font-semibold text-white transition hover:border-gold-400 hover:text-gold-300">Program Booklets</a>
            </div>
        </div>
        <div class="h-1 bg-gradient-to-r from-transparent via-gold-500 to-transparent"></div>
    </section>

    {{-- ============ Events ============ --}}
    <section id="events" class="scroll-mt-20 bg-neutral-50 py-16 sm:py-20">
        <div class="mx-auto max-w-7xl px-5 lg:px-8">
            <h2 class="text-center font-display text-4xl font-semibold text-ink sm:text-5xl">Graduation Events</h2>

            <div class="mt-12 grid gap-6 lg:grid-cols-3">
                @foreach ($events as $slug => $event)
                    <div id="{{ $slug }}" class="flex scroll-mt-24 flex-col [&>article]:flex-1">
                        @include('events._card', ['event' => $event, 'index' => $loop->iteration])
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ Program booklets ============ --}}
    <section id="programs" class="scroll-mt-20 bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-5xl px-5 lg:px-8">
            <h2 class="text-center font-display text-4xl font-semibold text-ink sm:text-5xl">Digital Program Booklets</h2>
            <p class="mx-auto mt-4 max-w-xl text-center text-neutral-600">The official program for each event, available to view or download once published.</p>

            <ul class="mt-10 divide-y divide-neutral-200 rounded-2xl border border-neutral-200">
                @foreach ($events as $slug => $event)
                    <li class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                        <div class="flex items-center gap-4">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-ink text-gold-400" aria-hidden="true">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linejoin="round" d="M6 3h8l4 4v14H6z" />
                                    <path stroke-linejoin="round" d="M14 3v4h4M9 12h6M9 16h6" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="font-display text-xl font-semibold text-ink">{{ $event['name'] }} Program</h3>
                                <p class="text-sm text-neutral-500">{{ $event['date_label'] }}</p>
                            </div>
                        </div>
                        @if ($event['program_url'])
                            <a href="{{ $event['program_url'] }}" target="_blank" rel="noopener"
                               class="inline-flex items-center justify-center rounded-full bg-gold-400 px-6 py-3 text-sm font-semibold text-ink transition hover:bg-gold-300">
                                View Program (PDF)
                            </a>
                        @else
                            <span class="inline-flex items-center justify-center rounded-full border border-neutral-200 px-6 py-3 text-sm font-semibold text-neutral-400">
                                Program coming soon
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ============ How it works ============ --}}
    <section class="bg-neutral-50 py-16 sm:py-20">
        <div class="mx-auto max-w-5xl px-5 lg:px-8">
            <h2 class="text-center font-display text-3xl font-semibold text-ink sm:text-4xl">Your Guide to Graduation</h2>

            <ol class="mt-10 grid gap-6 sm:grid-cols-3">
                @foreach ([
                    ['title' => 'Explore the events', 'body' => 'Review the date, time, venue, and dress code for each graduation event.'],
                    ['title' => 'RSVP to the Honors Ball', 'body' => 'Confirm your attendance online. You can update your response any time before the event.'],
                    ['title' => 'Bring your digital pass', 'body' => 'Your confirmation email includes an Event Pass with a QR code for check-in at the Honors Ball.'],
                ] as $step)
                    <li class="rounded-2xl border border-neutral-200 bg-white p-6">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-ink font-display text-lg font-semibold text-gold-400">{{ $loop->iteration }}</span>
                        <h3 class="mt-4 text-base font-semibold text-ink">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-neutral-600">{{ $step['body'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
@endsection
