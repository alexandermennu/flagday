{{--
    Protocols & Calendar page. Content comes from config/schedule.php; while
    `confirmed` is false, the dates are flagged as the previous year's, awaiting
    confirmation for this class.
--}}
@extends('layouts.public')

@php
    $schedule = config('schedule');
    $confirmed = (bool) $schedule['confirmed'];
    $classYear = config('graduation.class_year');
    $institution = config('graduation.institution');
@endphp

@section('title', 'Protocols & Calendar | '.$institution.' Class of '.$classYear)
@section('description', 'Graduation day protocols and the calendar of events for the '.$institution.' Class of '.$classYear.'.')

@section('content')
    <section class="relative isolate overflow-hidden bg-ink text-white">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_80%_-10%,rgba(231,185,58,0.3),transparent_55%)]"></div>
        <div class="mx-auto max-w-5xl px-5 pb-12 pt-12 sm:pb-16 sm:pt-16 lg:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-gold-400">Class of {{ $classYear }}</p>
            <h1 class="mt-3 font-display text-5xl font-bold sm:text-6xl">
                <span class="text-gold-gradient">Protocols &amp; Calendar</span>
            </h1>
            <p class="mt-3 text-sm font-semibold uppercase tracking-[0.2em] text-neutral-400">{{ $institution }}</p>
            <p class="mt-6 max-w-2xl text-lg leading-relaxed text-neutral-300">
                Arrival protocols for graduation day and the calendar of graduation events.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#protocols" class="inline-flex items-center justify-center rounded-full border border-white/25 px-6 py-3 text-sm font-semibold text-white transition hover:border-gold-400 hover:text-gold-300">Protocols</a>
                <a href="#calendar" class="inline-flex items-center justify-center rounded-full border border-white/25 px-6 py-3 text-sm font-semibold text-white transition hover:border-gold-400 hover:text-gold-300">Calendar of Events</a>
            </div>
        </div>
        <div class="h-1 bg-gradient-to-r from-transparent via-gold-500 to-transparent"></div>
    </section>

    @unless ($confirmed)
        <div class="bg-gold-50" data-schedule-notice>
            <div class="mx-auto flex max-w-5xl items-start gap-3 px-5 py-4 lg:px-8">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-gold-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" /></svg>
                <p class="text-sm leading-relaxed text-neutral-800">
                    <strong class="font-semibold text-ink">Awaiting confirmation for the Class of {{ $classYear }}.</strong>
                    The protocols and calendar below are from the {{ $schedule['source_year'] }} graduation program and are shown for reference.
                    The Class of {{ $classYear }} dates, times, venues and speakers will be published here once approved.
                </p>
            </div>
        </div>
    @endunless

    {{-- ============ Protocols ============ --}}
    <section id="protocols" class="scroll-mt-20 bg-white py-14 sm:py-16">
        <div class="mx-auto max-w-5xl px-5 lg:px-8">
            <h2 class="font-display text-4xl font-semibold text-ink sm:text-5xl">Protocols</h2>
            <div class="mt-5 h-1 w-14 bg-gold-500" aria-hidden="true"></div>

            <ol class="mt-10 border-l-2 border-gold-500/40">
                @foreach ($schedule['protocols'] as $step)
                    <li class="relative pb-8 pl-8 last:pb-0 sm:pl-10">
                        <span class="absolute -left-[9px] top-1.5 flex h-4 w-4 items-center justify-center bg-ink ring-4 ring-white" aria-hidden="true">
                            <span class="h-1.5 w-1.5 bg-gold-400"></span>
                        </span>
                        <div class="sm:grid sm:grid-cols-[9rem_minmax(0,1fr)] sm:gap-6">
                            <p class="text-sm font-semibold uppercase tracking-[0.12em] text-gold-700">{{ $step['time'] }}</p>
                            <p class="mt-1 leading-relaxed text-neutral-700 sm:mt-0">{{ $step['text'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ============ Calendar of Events ============ --}}
    <section id="calendar" class="scroll-mt-20 bg-neutral-50 py-14 sm:py-16">
        <div class="mx-auto max-w-5xl px-5 lg:px-8">
            <h2 class="font-display text-4xl font-semibold text-ink sm:text-5xl">Calendar of Events</h2>
            <div class="mt-5 h-1 w-14 bg-gold-500" aria-hidden="true"></div>
            @if ($schedule['theme'])
                <p class="mt-5 text-neutral-700">
                    <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gold-700">Theme{{ $confirmed ? '' : ' ('.$schedule['source_year'].')' }}</span><br>
                    <span class="font-display text-xl font-semibold text-ink">{{ $schedule['theme'] }}</span>
                </p>
            @endif

            <div class="mt-10 grid gap-5 md:grid-cols-2">
                @foreach ($schedule['calendar'] as $entry)
                    <article class="flex flex-col border border-neutral-200 bg-white" data-calendar-entry>
                        <div class="bg-ink px-5 pb-5 pt-6 text-white">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gold-400">{{ $entry['date'] }}</p>
                                @unless ($confirmed)
                                    <span class="border border-gold-400/50 bg-gold-400/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-gold-300">
                                        {{ $schedule['source_year'] }} date &middot; to be confirmed
                                    </span>
                                @endunless
                            </div>
                            <h3 class="mt-2 font-display text-2xl font-semibold">{{ $entry['event'] }}</h3>
                            @if ($entry['tag'])
                                <p class="mt-1 text-sm text-neutral-300">({{ $entry['tag'] }})</p>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col px-5 py-5">
                            <dl class="space-y-3 text-sm">
                                @foreach (['Time' => $entry['time'], 'Venue' => $entry['venue'], ($entry['speaker_label'] ?? 'Speaker') => $entry['speaker'], 'Color' => $entry['color']] as $label => $value)
                                    @if ($value)
                                        <div class="flex items-start gap-3">
                                            <dt class="w-24 shrink-0 pt-0.5 text-[11px] font-semibold uppercase tracking-wider text-neutral-400">{{ $label }}</dt>
                                            <dd class="font-semibold text-neutral-900">{{ $value }}</dd>
                                        </div>
                                    @endif
                                @endforeach
                            </dl>
                            @foreach ($entry['notes'] as $note)
                                <p class="mt-4 border-t border-neutral-100 pt-4 text-sm leading-relaxed text-neutral-600">{{ $note }}</p>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
