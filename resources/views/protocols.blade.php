{{--
    Protocols & Calendar page: protocols on the left, calendar of events on the right (as in
    the printed program), stacked on smaller screens. Content: admin → Protocols & Calendar
    (ProtocolsController). Until the schedule is confirmed, dates are flagged as the source
    year's, awaiting confirmation for this class.
--}}
@extends('layouts.public')

@php
    $confirmed = (bool) $settings->is_confirmed;
    $sourceYear = $settings->source_year;
    $classYear = config('graduation.class_year');
    $institution = config('graduation.institution');
@endphp

@section('title', 'Protocols & Calendar | '.$institution.' Class of '.$classYear)
@section('description', 'Graduation day protocols and the calendar of events for the '.$institution.' Class of '.$classYear.'.')

@section('content')
    <section class="relative isolate overflow-hidden bg-ink text-white">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_80%_-10%,rgba(231,185,58,0.3),transparent_55%)]"></div>
        <div class="mx-auto max-w-7xl px-5 pb-10 pt-10 sm:pb-12 sm:pt-12 lg:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gold-400">Class of {{ $classYear }}</p>
            <h1 class="mt-3 font-display text-4xl font-bold sm:text-5xl">
                <span class="text-gold-gradient">Protocols &amp; Calendar</span>
            </h1>
            <p class="mt-4 max-w-2xl text-base leading-relaxed text-neutral-300 sm:text-lg">
                Arrival protocols for graduation day and the calendar of graduation events.
            </p>
        </div>
        <div class="h-1 bg-gradient-to-r from-transparent via-gold-500 to-transparent"></div>
    </section>

    @unless ($confirmed)
        <div class="bg-gold-50" data-schedule-notice>
            <div class="mx-auto flex max-w-7xl items-start gap-3 px-5 py-4 lg:px-8">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-gold-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" /></svg>
                <p class="text-sm leading-relaxed text-neutral-800">
                    <strong class="font-semibold text-ink">Awaiting confirmation for the Class of {{ $classYear }}.</strong>
                    The protocols and calendar below are from the {{ $sourceYear ?: 'previous' }} graduation program and are shown for reference.
                    The Class of {{ $classYear }} dates, times, venues and speakers will be published here once approved.
                </p>
            </div>
        </div>
    @endunless

    <section class="bg-white py-12 sm:py-14">
        <div class="mx-auto grid max-w-7xl gap-12 px-5 lg:grid-cols-2 lg:gap-0 lg:divide-x lg:divide-gold-500/30 lg:px-8">

            {{-- ============ Protocols ============ --}}
            <div id="protocols" class="min-w-0 scroll-mt-24 lg:pr-12">
                <h2 class="font-display text-3xl font-semibold text-ink sm:text-4xl">Protocols</h2>
                <div class="mt-4 h-1 w-12 bg-gold-500" aria-hidden="true"></div>

                @if ($protocols->isEmpty())
                    <p class="mt-8 text-neutral-500">The graduation-day protocols will be published here.</p>
                @else
                    <ol class="mt-8 border-l-2 border-gold-500/40">
                        @foreach ($protocols as $protocol)
                            <li class="relative pb-6 pl-7 last:pb-0" data-protocol>
                                <span class="absolute -left-[9px] top-1 flex h-4 w-4 items-center justify-center bg-ink ring-4 ring-white" aria-hidden="true">
                                    <span class="h-1.5 w-1.5 bg-gold-400"></span>
                                </span>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-gold-700">{{ $protocol->time_label }}</p>
                                <p class="mt-1 leading-relaxed text-neutral-700">{{ $protocol->description }}</p>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>

            {{-- ============ Calendar of Events ============ --}}
            <div id="calendar" class="min-w-0 scroll-mt-24 lg:pl-12">
                <h2 class="font-display text-3xl font-semibold text-ink sm:text-4xl">Calendar of Events</h2>
                <div class="mt-4 h-1 w-12 bg-gold-500" aria-hidden="true"></div>

                @if ($settings->theme)
                    <p class="mt-6">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-700">Theme{{ $confirmed || ! $sourceYear ? '' : ' ('.$sourceYear.')' }}</span>
                        <span class="mt-1 block font-display text-xl font-semibold text-ink">{{ $settings->theme }}</span>
                    </p>
                @endif

                @if ($events->isEmpty())
                    <p class="mt-8 text-neutral-500">The calendar of events will be published here.</p>
                @else
                    <div class="mt-8 space-y-6">
                        @foreach ($events as $event)
                            @php
                                $details = array_filter([
                                    'Time' => $event->timeLabel(),
                                    'Venue' => $event->venue,
                                    'Theme' => $event->theme,
                                    ($event->speaker_label ?: 'Speaker') => $event->speaker,
                                    'Guest of honor' => $event->guest_of_honor,
                                    ($event->dress_code_label ?: 'Dress code') => $event->dress_code,
                                ]);
                            @endphp
                            <article class="border-l-2 border-gold-500 bg-neutral-50 px-5 py-4" data-calendar-entry>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <p class="text-xs font-bold uppercase tracking-[0.15em] text-ink">{{ $event->dateLabel() ?? 'Date to be announced' }}</p>
                                    @if (! $confirmed && $event->event_date)
                                        <span class="border border-gold-500/50 bg-gold-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-gold-800">
                                            {{ $sourceYear ?: 'Previous' }} date &middot; to be confirmed
                                        </span>
                                    @endif
                                </div>
                                <h3 class="mt-1.5 font-display text-xl font-semibold text-ink">{{ $event->title }}</h3>
                                @if ($event->attendance)
                                    <p class="text-sm text-neutral-600">({{ $event->attendance }})</p>
                                @endif

                                @if ($details)
                                    <dl class="mt-3 grid grid-cols-[6.5rem_minmax(0,1fr)] gap-x-3 gap-y-1.5 text-sm">
                                        @foreach ($details as $label => $value)
                                            <dt class="pt-px text-[11px] font-semibold uppercase tracking-wider text-neutral-500">{{ $label }}</dt>
                                            <dd class="font-semibold text-neutral-900">{{ $value }}</dd>
                                        @endforeach
                                    </dl>
                                @endif

                                @if ($event->description)
                                    <p class="mt-3 text-sm leading-relaxed text-neutral-700">{{ $event->description }}</p>
                                @endif
                                @foreach ($event->notes ?? [] as $note)
                                    <p class="mt-2 text-sm leading-relaxed text-neutral-700">{{ $note }}</p>
                                @endforeach
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
