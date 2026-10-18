{{-- Public detail page for one graduation event. Expects $event and $otherEvents. --}}
@extends('layouts.public')

@section('title', $event['name'].' | '.config('graduation.institution').' Class of '.config('graduation.class_year'))
@section('description', $event['summary'])

@php
    $details = [
        ['label' => 'Date', 'value' => $event['date_label'], 'set' => filled($event['date'])],
        ['label' => 'Time', 'value' => $event['time_label'], 'set' => filled($event['time'])],
        ['label' => 'Venue', 'value' => $event['venue_label'], 'set' => filled($event['venue']), 'sub' => $event['venue_address']],
        ['label' => 'Dress Code', 'value' => $event['dress_code_label'], 'set' => filled($event['dress_code'])],
    ];
@endphp

@section('content')
    <section class="relative isolate overflow-hidden bg-ink text-white">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_80%_-10%,rgba(231,185,58,0.3),transparent_55%)]"></div>
        <div class="mx-auto max-w-5xl px-5 pb-14 pt-10 sm:pb-20 sm:pt-14 lg:px-8">
            <a href="{{ route('class.show') }}#events" class="inline-flex items-center gap-1.5 text-sm font-medium text-neutral-400 transition hover:text-gold-300">
                &larr; Class of {{ config('graduation.class_year') }} events
            </a>
            <p class="mt-8 text-xs font-semibold uppercase tracking-[0.3em] text-gold-400">{{ $event['kicker'] }}</p>
            <h1 class="mt-3 font-display text-5xl font-bold sm:text-7xl">
                <span class="text-gold-gradient">{{ $event['name'] }}</span>
            </h1>
            <p class="mt-3 text-sm font-semibold uppercase tracking-[0.2em] text-neutral-400">{{ config('graduation.institution') }} &middot; Class of {{ config('graduation.class_year') }}</p>
            <p class="mt-6 max-w-2xl text-lg leading-relaxed text-neutral-300">{{ $event['description'] }}</p>

            @if ($event['rsvp'])
                <a href="{{ route('rsvp.create') }}"
                   class="mt-8 inline-flex w-full items-center justify-center rounded-full bg-gold-400 px-8 py-4 text-base font-semibold text-ink shadow-lg shadow-gold-500/25 transition hover:bg-gold-300 active:scale-[0.98] sm:w-auto">
                    RSVP for the {{ $event['name'] }}
                </a>
            @endif
        </div>
        <div class="h-1 bg-gradient-to-r from-transparent via-gold-500 to-transparent"></div>
    </section>

    <section class="bg-neutral-50 py-14 sm:py-20">
        <div class="mx-auto grid max-w-5xl gap-8 px-5 lg:grid-cols-3 lg:px-8">
            <div class="lg:col-span-2">
                <h2 class="font-display text-3xl font-semibold text-ink">Event Details</h2>
                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    @foreach ($details as $detail)
                        <div class="rounded-2xl border border-neutral-200 bg-white p-5">
                            <dt class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gold-600">{{ $detail['label'] }}</dt>
                            <dd class="mt-2 text-lg font-semibold {{ $detail['set'] ? 'text-ink' : 'italic font-medium text-neutral-400' }}">{{ $detail['value'] }}</dd>
                            @if (! empty($detail['sub']))
                                <dd class="mt-0.5 text-sm text-neutral-500">{{ $detail['sub'] }}</dd>
                            @endif
                        </div>
                    @endforeach
                </dl>
                <p class="mt-4 text-sm text-neutral-500">Details marked “{{ \App\Support\Graduation::PLACEHOLDER }}” will be published here once confirmed.</p>
            </div>

            <aside class="space-y-4">
                <div class="rounded-2xl bg-ink p-6 text-white">
                    <h2 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gold-400">Event Program</h2>
                    @if ($event['program_url'])
                        <p class="mt-3 text-sm text-neutral-300">Download the official program for the {{ $event['name'] }}.</p>
                        @if ($event['flipbook_url'])
                            <a href="{{ $event['flipbook_url'] }}"
                               class="mt-5 inline-flex w-full items-center justify-center rounded-full bg-gold-400 px-5 py-3 text-sm font-semibold text-ink transition hover:bg-gold-300">
                                View Program
                            </a>
                        @else
                            <a href="{{ $event['program_url'] }}" target="_blank" rel="noopener"
                               class="mt-5 inline-flex w-full items-center justify-center rounded-full bg-gold-400 px-5 py-3 text-sm font-semibold text-ink transition hover:bg-gold-300">
                                View Program (PDF)
                            </a>
                        @endif
                        @if ($event['program_download_url'])
                            <a href="{{ $event['program_download_url'] }}"
                               class="mt-3 inline-flex w-full items-center justify-center rounded-full border border-white/20 px-5 py-3 text-sm font-semibold text-white transition hover:border-gold-400 hover:text-gold-300">
                                Download PDF
                            </a>
                        @endif
                    @else
                        <p class="mt-3 text-sm text-neutral-300">The program for this event will be available here soon.</p>
                        <span class="mt-5 inline-flex w-full items-center justify-center rounded-full border border-white/15 px-5 py-3 text-sm font-semibold text-neutral-400">
                            Program coming soon
                        </span>
                    @endif
                </div>

                @if ($event['rsvp'])
                    <div class="rounded-2xl border border-gold-300 bg-gold-50 p-6">
                        <h2 class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gold-700">RSVP</h2>
                        <p class="mt-3 text-sm text-neutral-700">Confirm your attendance and receive your digital Event Pass by email.</p>
                        <a href="{{ route('rsvp.create') }}" class="mt-4 inline-flex text-sm font-semibold text-ink underline decoration-gold-500 decoration-2 underline-offset-4 hover:text-gold-700">
                            Go to the RSVP form &rarr;
                        </a>
                    </div>
                @endif
            </aside>
        </div>
    </section>

    <section class="bg-white py-14 sm:py-20">
        <div class="mx-auto max-w-5xl px-5 lg:px-8">
            <h2 class="font-display text-3xl font-semibold text-ink">More Graduation Events</h2>
            <div class="mt-8 grid gap-6 md:grid-cols-2">
                @foreach ($otherEvents as $other)
                    @include('events._card', ['event' => $other, 'index' => array_search($other['slug'], array_keys(config('graduation.events'))) + 1])
                @endforeach
            </div>
        </div>
    </section>
@endsection
