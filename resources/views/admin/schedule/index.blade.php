@extends('admin.layout')

@section('title', 'Protocols & Calendar')

@php
    $in = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-gold-500 focus:outline-none focus:ring-1 focus:ring-gold-500';
    $label = 'mb-1 block text-sm font-semibold text-slate-700';
    $help = 'mt-1 text-xs text-slate-500';
    $iconBtn = 'h-9 w-9 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-100 disabled:opacity-30';
@endphp

@section('content')
    <p class="mb-6 max-w-3xl text-sm text-slate-500">
        Content of the public <a href="{{ route('protocols.show') }}" target="_blank" rel="noopener" class="font-semibold text-ink underline">Protocols &amp; Calendar page</a>.
        Only published entries appear there, in the order below.
    </p>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">Please correct the following:</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-6">
        {{-- ============ Settings ============ --}}
        <form method="POST" action="{{ route('admin.schedule.settings.update') }}" class="rounded-2xl border border-slate-200 bg-white p-6">
            @csrf
            @method('PUT')
            <h2 class="text-base font-bold text-ink">Page settings</h2>

            <div class="mt-5 grid gap-5 md:grid-cols-[minmax(0,1fr)_10rem]">
                <div>
                    <label for="theme" class="{{ $label }}">Calendar theme</label>
                    <input type="text" name="theme" id="theme" maxlength="160" value="{{ old('theme', $settings->theme) }}" class="{{ $in }}">
                    <p class="{{ $help }}">Shown at the top of the Calendar of Events. Leave blank to hide.</p>
                </div>
                <div>
                    <label for="source_year" class="{{ $label }}">Source year</label>
                    <input type="text" name="source_year" id="source_year" inputmode="numeric" maxlength="4" value="{{ old('source_year', $settings->source_year) }}" class="{{ $in }}">
                    <p class="{{ $help }}">Year the unconfirmed details come from.</p>
                </div>
            </div>

            <label class="mt-5 flex items-start gap-2 text-sm font-medium text-slate-700">
                <input type="hidden" name="is_confirmed" value="0">
                <input type="checkbox" name="is_confirmed" value="1" @checked(old('is_confirmed', $settings->is_confirmed)) class="mt-0.5 h-4 w-4 rounded accent-gold-500">
                <span>
                    The Class of {{ config('graduation.class_year') }} schedule is confirmed
                    <span class="block text-xs font-normal text-slate-500">
                        While unticked, the page shows an “awaiting confirmation” notice and tags every date as a {{ $settings->source_year ?: 'previous-year' }} date to be confirmed.
                        Tick it only once the approved {{ config('graduation.class_year') }} details are entered.
                    </span>
                </span>
            </label>

            <button type="submit" class="mt-5 inline-flex items-center rounded-lg bg-ink px-5 py-2.5 text-sm font-semibold text-gold-300 transition hover:bg-neutral-800">Save settings</button>
        </form>

        {{-- ============ Protocols ============ --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-base font-bold text-ink">Protocols</h2>
            <p class="{{ $help }}">Graduation-day arrival steps. Use the arrows to reorder, then save.</p>

            @if ($protocols->isEmpty())
                <p class="mt-5 rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500">No protocols yet — add one below.</p>
            @else
                <form method="POST" action="{{ route('admin.schedule.protocols.update') }}" class="mt-5">
                    @csrf
                    @method('PUT')
                    <ol class="space-y-3" data-sortable>
                        @foreach ($protocols as $protocol)
                            <li data-sortable-row class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-3 md:flex-row md:items-start">
                                <input type="hidden" name="order[]" value="{{ $protocol->id }}">
                                <span data-sortable-number class="hidden w-6 pt-2 text-right text-xs font-semibold text-slate-400 md:block">{{ $loop->iteration }}</span>
                                <div class="md:w-44">
                                    <input type="text" name="protocols[{{ $protocol->id }}][time_label]" value="{{ old("protocols.{$protocol->id}.time_label", $protocol->time_label) }}"
                                           required maxlength="60" aria-label="Time" placeholder="e.g. From 8:00 a.m." class="{{ $in }}">
                                    <label class="mt-2 flex items-center gap-2 text-sm font-medium text-slate-700">
                                        <input type="hidden" name="protocols[{{ $protocol->id }}][is_published]" value="0">
                                        <input type="checkbox" name="protocols[{{ $protocol->id }}][is_published]" value="1" @checked(old("protocols.{$protocol->id}.is_published", $protocol->is_published)) class="h-4 w-4 rounded accent-gold-500">
                                        Published
                                    </label>
                                </div>
                                <textarea name="protocols[{{ $protocol->id }}][description]" rows="2" required maxlength="1000" aria-label="Description"
                                          class="{{ $in }} flex-1">{{ old("protocols.{$protocol->id}.description", $protocol->description) }}</textarea>
                                <div class="flex shrink-0 items-start gap-1">
                                    <button type="button" data-sort="up" aria-label="Move up" title="Move up" class="{{ $iconBtn }}">↑</button>
                                    <button type="button" data-sort="down" aria-label="Move down" title="Move down" class="{{ $iconBtn }}">↓</button>
                                    <button type="submit" form="delete-protocol-{{ $protocol->id }}" aria-label="Delete protocol" title="Delete"
                                            onclick="return confirm('Delete this protocol? This cannot be undone.')"
                                            class="h-9 w-9 rounded-lg border border-slate-200 text-red-600 hover:bg-red-50">✕</button>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                    <button type="submit" class="mt-4 inline-flex items-center rounded-lg bg-ink px-5 py-2.5 text-sm font-semibold text-gold-300 transition hover:bg-neutral-800">Save protocols</button>
                </form>
                @foreach ($protocols as $protocol)
                    <form method="POST" action="{{ route('admin.schedule.protocols.destroy', $protocol) }}" id="delete-protocol-{{ $protocol->id }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            @endif

            <form method="POST" action="{{ route('admin.schedule.protocols.store') }}" class="mt-6 border-t border-slate-200 pt-5">
                @csrf
                <h3 class="text-sm font-bold text-ink">Add a protocol</h3>
                <div class="mt-3 flex flex-col gap-3 md:flex-row md:items-start">
                    <input type="text" name="time_label" required maxlength="60" placeholder="Time, e.g. At 10:45 a.m." aria-label="New protocol time" class="{{ $in }} md:w-52">
                    <textarea name="description" rows="2" required maxlength="1000" placeholder="Description" aria-label="New protocol description" class="{{ $in }} flex-1"></textarea>
                    <button type="submit" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-gold-400 px-5 py-2.5 text-sm font-semibold text-ink transition hover:bg-gold-300">Add</button>
                </div>
            </form>
        </div>

        {{-- ============ Calendar of Events ============ --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-ink">Calendar of Events</h2>
                    <p class="{{ $help }}">Reorder with the arrows (then save), or sort by date and time.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if ($events->count() > 1)
                        <form method="POST" action="{{ route('admin.schedule.events.sort') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Sort by date</button>
                        </form>
                    @endif
                    <a href="{{ route('admin.schedule.events.create') }}" class="inline-flex items-center rounded-lg bg-gold-400 px-4 py-2 text-sm font-semibold text-ink transition hover:bg-gold-300">+ Add event</a>
                </div>
            </div>

            @if ($events->isEmpty())
                <p class="mt-5 rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center text-sm text-slate-500">No calendar events yet.</p>
            @else
                <form method="POST" action="{{ route('admin.schedule.events.order') }}" class="mt-5">
                    @csrf
                    @method('PUT')
                    <ol class="space-y-3" data-sortable>
                        @foreach ($events as $event)
                            <li data-sortable-row class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-3 sm:flex-row sm:items-center">
                                <input type="hidden" name="order[]" value="{{ $event->id }}">
                                <span data-sortable-number class="hidden w-6 text-right text-xs font-semibold text-slate-400 sm:block">{{ $loop->iteration }}</span>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('admin.schedule.events.edit', $event) }}" class="font-semibold text-ink hover:underline">{{ $event->title }}</a>
                                    <p class="text-xs text-slate-500">
                                        {{ $event->dateLabel() ?? 'No date' }}@if ($event->timeLabel()) &middot; {{ $event->timeLabel() }}@endif
                                        @if ($event->venue) &middot; {{ $event->venue }}@endif
                                    </p>
                                </div>
                                <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                    <input type="hidden" name="published[{{ $event->id }}]" value="0">
                                    <input type="checkbox" name="published[{{ $event->id }}]" value="1" @checked($event->is_published) class="h-4 w-4 rounded accent-gold-500">
                                    Published
                                </label>
                                <div class="flex shrink-0 items-center gap-1">
                                    <a href="{{ route('admin.schedule.events.edit', $event) }}" class="inline-flex h-9 items-center rounded-lg border border-slate-200 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-100">Edit</a>
                                    <button type="button" data-sort="up" aria-label="Move up" title="Move up" class="{{ $iconBtn }}">↑</button>
                                    <button type="button" data-sort="down" aria-label="Move down" title="Move down" class="{{ $iconBtn }}">↓</button>
                                    <button type="submit" form="delete-event-{{ $event->id }}" aria-label="Delete event" title="Delete"
                                            onclick="return confirm('Delete “{{ addslashes($event->title) }}”? This cannot be undone.')"
                                            class="h-9 w-9 rounded-lg border border-slate-200 text-red-600 hover:bg-red-50">✕</button>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                    <button type="submit" class="mt-4 inline-flex items-center rounded-lg bg-ink px-5 py-2.5 text-sm font-semibold text-gold-300 transition hover:bg-neutral-800">Save order &amp; publishing</button>
                </form>
                @foreach ($events as $event)
                    <form method="POST" action="{{ route('admin.schedule.events.destroy', $event) }}" id="delete-event-{{ $event->id }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            @endif
        </div>
    </div>
@endsection
