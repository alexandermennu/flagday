{{--
    The "Class of 2026" page — every graduation event with its published details.
    Content comes from config/graduation.php via App\Support\Graduation.
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
                Three occasions to honor the Class of {{ $classYear }}. Details are published here as they are
                confirmed, so check back before each event.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#events" class="inline-flex items-center justify-center rounded-full border border-white/25 px-6 py-3 text-sm font-semibold text-white transition hover:border-gold-400 hover:text-gold-300">Graduation Events</a>
            </div>
        </div>
        <div class="h-1 bg-gradient-to-r from-transparent via-gold-500 to-transparent"></div>
    </section>

    {{-- ============ Events ============ --}}
    {{-- Desktop (xl): introduction on the left (30% including the gap), the three cards in one row on the right (70%).
         Below xl the introduction sits above the cards: three across from lg, stacked on smaller screens. --}}
    <section id="events" class="scroll-mt-20 bg-neutral-50 py-14 sm:py-16">
        <div class="mx-auto max-w-7xl px-5 lg:px-8 xl:grid xl:grid-cols-[minmax(0,1fr)_70%] xl:items-center xl:gap-10">
            <div class="max-w-xl xl:max-w-none">
                <h2 class="font-display text-4xl font-semibold text-ink sm:text-5xl">Graduation Events</h2>
                <div class="mt-5 h-1 w-14 bg-gold-500" aria-hidden="true"></div>
                <p class="mt-5 leading-relaxed text-neutral-600">
                    Three occasions to celebrate the Class of {{ $classYear }}. Select an event for its date, venue,
                    dress code and program.
                </p>
            </div>

            <div class="mt-10 grid gap-5 lg:grid-cols-3 xl:mt-0">
                @foreach ($events as $slug => $event)
                    <div id="{{ $slug }}" class="flex min-w-0 scroll-mt-24 flex-col [&>article]:flex-1">
                        @include('events._card', ['event' => $event, 'index' => $loop->iteration, 'square' => true])
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
