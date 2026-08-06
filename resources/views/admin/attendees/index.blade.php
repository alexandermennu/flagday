@extends('admin.layout')

@section('title', 'Attendees')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.attendees.index') }}" class="flex flex-1 gap-3">
            <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search name or email…"
                   class="w-full max-w-xs rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
            <select name="status" onchange="this.form.submit()"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                <option value="">All statuses</option>
                @foreach (\App\Enums\AttendeeStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected($filters['status'] === $case->value)>{{ $case->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Search</button>
        </form>

        <div class="flex gap-3">
            <a href="{{ route('admin.attendees.export') }}"
               class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Export CSV
            </a>
            <a href="{{ route('admin.attendees.create') }}"
               class="inline-flex items-center rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-800">
                + Add Attendee
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.attendees.remind') }}" id="remind-form">
        @csrf
        <div class="mb-3 flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" id="select-all" class="rounded accent-blue-950">
                Select all on this page
            </label>
            <button type="submit" id="remind-button" disabled
                    class="inline-flex items-center rounded-lg bg-blue-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-900 disabled:cursor-not-allowed disabled:opacity-40">
                Send Reminder to Selected
            </button>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-400">
                        <th class="w-10 px-4 py-3"></th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Organization</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Checked In</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendees as $attendee)
                        <tr class="border-b border-slate-50">
                            <td class="px-4 py-3">
                                <input type="checkbox" name="attendee_ids[]" value="{{ $attendee->id }}" class="row-checkbox rounded accent-blue-950">
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $attendee->full_name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $attendee->email }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $attendee->organization ?? '—' }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$attendee->status" /></td>
                            <td class="px-4 py-3 text-slate-500">
                                @if ($attendee->checked_in_at)
                                    <span class="text-green-700">{{ $attendee->checked_in_at->format('M j, g:i A') }}</span>
                                @else
                                    &mdash;
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route('admin.attendees.edit', $attendee) }}" class="font-medium text-blue-950 hover:underline">Edit</a>
                                @if ($attendee->checked_in_at)
                                    <button type="submit" form="uncheckin-{{ $attendee->id }}" class="ml-3 font-medium text-amber-600 hover:underline">Undo check-in</button>
                                @endif
                                <button type="submit" form="delete-{{ $attendee->id }}" class="ml-3 font-medium text-red-700 hover:underline"
                                        onclick="return confirm('Remove {{ $attendee->full_name }}?')">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-400">No attendees found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

    {{-- Row-action forms live outside the bulk-remind form since HTML forms can't nest --}}
    @foreach ($attendees as $attendee)
        <form id="delete-{{ $attendee->id }}" method="POST" action="{{ route('admin.attendees.destroy', $attendee) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
        @if ($attendee->checked_in_at)
            <form id="uncheckin-{{ $attendee->id }}" method="POST" action="{{ route('admin.attendees.uncheck-in', $attendee) }}" class="hidden">
                @csrf
                @method('PATCH')
            </form>
        @endif
    @endforeach

    <div class="mt-6">
        {{ $attendees->links() }}
    </div>
@endsection
