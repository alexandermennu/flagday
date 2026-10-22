@extends('admin.layout')

@section('title', $event->exists ? 'Edit Calendar Event' : 'Add Calendar Event')

@php
    $in = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-gold-500 focus:outline-none focus:ring-1 focus:ring-gold-500';
    $label = 'mb-1 block text-sm font-semibold text-slate-700';
    $help = 'mt-1 text-xs text-slate-500';
    $field = fn (string $name) => old($name, $name === 'event_date' ? $event->event_date?->format('Y-m-d') : $event->{$name});
@endphp

@section('content')
    <a href="{{ route('admin.schedule.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">&larr; Protocols &amp; Calendar</a>

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">Please correct the following:</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $event->exists ? route('admin.schedule.events.update', $event) : route('admin.schedule.events.store') }}"
          class="mt-4 space-y-6 rounded-2xl border border-slate-200 bg-white p-6">
        @csrf
        @if ($event->exists) @method('PUT') @endif

        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="title" class="{{ $label }}">Event title <span class="text-red-600">*</span></label>
                <input type="text" name="title" id="title" required maxlength="120" value="{{ $field('title') }}" class="{{ $in }}">
            </div>
            <div>
                <label for="event_date" class="{{ $label }}">Date</label>
                <input type="date" name="event_date" id="event_date" value="{{ $field('event_date') }}" class="{{ $in }}">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_time" class="{{ $label }}">Start time</label>
                    <input type="time" name="start_time" id="start_time" value="{{ $field('start_time') }}" class="{{ $in }}">
                </div>
                <div>
                    <label for="end_time" class="{{ $label }}">End time</label>
                    <input type="time" name="end_time" id="end_time" value="{{ $field('end_time') }}" class="{{ $in }}">
                </div>
            </div>
            <div>
                <label for="time_note" class="{{ $label }}">Time note</label>
                <input type="text" name="time_note" id="time_note" maxlength="60" value="{{ $field('time_note') }}" placeholder="e.g. Prompt" class="{{ $in }}">
                <p class="{{ $help }}">Printed after the time, e.g. “7:30 p.m. Prompt”.</p>
            </div>
            <div>
                <label for="venue" class="{{ $label }}">Venue</label>
                <input type="text" name="venue" id="venue" maxlength="160" value="{{ $field('venue') }}" class="{{ $in }}">
            </div>
            <div>
                <label for="attendance" class="{{ $label }}">Attendance instruction</label>
                <input type="text" name="attendance" id="attendance" maxlength="160" value="{{ $field('attendance') }}" placeholder="e.g. Attendance Mandatory" class="{{ $in }}">
                <p class="{{ $help }}">Shown in brackets under the event title.</p>
            </div>
            <div>
                <label for="theme" class="{{ $label }}">Theme</label>
                <input type="text" name="theme" id="theme" maxlength="160" value="{{ $field('theme') }}" class="{{ $in }}">
                <p class="{{ $help }}">Only if this event has its own theme (the calendar theme is in page settings).</p>
            </div>
            <div class="grid grid-cols-[9rem_minmax(0,1fr)] gap-3">
                <div>
                    <label for="speaker_label" class="{{ $label }}">Speaker label</label>
                    <input type="text" name="speaker_label" id="speaker_label" maxlength="40" value="{{ $field('speaker_label') }}" placeholder="Speaker" class="{{ $in }}">
                </div>
                <div>
                    <label for="speaker" class="{{ $label }}">Speaker</label>
                    <input type="text" name="speaker" id="speaker" maxlength="160" value="{{ $field('speaker') }}" class="{{ $in }}">
                </div>
            </div>
            <div>
                <label for="guest_of_honor" class="{{ $label }}">Guest of honor</label>
                <input type="text" name="guest_of_honor" id="guest_of_honor" maxlength="160" value="{{ $field('guest_of_honor') }}" class="{{ $in }}">
            </div>
            <div class="grid grid-cols-[9rem_minmax(0,1fr)] gap-3">
                <div>
                    <label for="dress_code_label" class="{{ $label }}">Dress label</label>
                    <input type="text" name="dress_code_label" id="dress_code_label" maxlength="40" value="{{ $field('dress_code_label') }}" placeholder="Dress code" class="{{ $in }}">
                </div>
                <div>
                    <label for="dress_code" class="{{ $label }}">Dress code / colors</label>
                    <input type="text" name="dress_code" id="dress_code" maxlength="120" value="{{ $field('dress_code') }}" class="{{ $in }}">
                </div>
            </div>
            <div class="md:col-span-2">
                <label for="description" class="{{ $label }}">Description</label>
                <textarea name="description" id="description" rows="3" maxlength="2000" class="{{ $in }}">{{ $field('description') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label for="notes_text" class="{{ $label }}">Additional information lines</label>
                <textarea name="notes_text" id="notes_text" rows="4" maxlength="5000" class="{{ $in }}">{{ old('notes_text', implode("\n", $event->notes ?? [])) }}</textarea>
                <p class="{{ $help }}">One line per item, e.g. reporting or seating instructions. Each line is shown as its own paragraph.</p>
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $event->is_published)) class="h-4 w-4 rounded accent-gold-500">
            Published (shown on the public page)
        </label>

        <div class="flex flex-wrap items-center gap-3 border-t border-slate-200 pt-5">
            <button type="submit" class="inline-flex items-center rounded-lg bg-ink px-5 py-2.5 text-sm font-semibold text-gold-300 transition hover:bg-neutral-800">
                {{ $event->exists ? 'Save event' : 'Add event' }}
            </button>
            <a href="{{ route('admin.schedule.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Cancel</a>
        </div>
    </form>

    @if ($event->exists)
        <form method="POST" action="{{ route('admin.schedule.events.destroy', $event) }}" class="mt-6"
              onsubmit="return confirm('Delete this calendar event? This cannot be undone.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm font-semibold text-red-700 hover:text-red-800">Delete event</button>
        </form>
    @endif
@endsection
