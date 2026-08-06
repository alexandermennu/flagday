@extends('admin.layout')

@section('title', 'Reports')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Invited</p>
            <p class="mt-1 text-2xl font-bold text-blue-950">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Response Rate</p>
            <p class="mt-1 text-2xl font-bold text-blue-950">{{ $stats['response_rate'] }}%</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Confirmed</p>
            <p class="mt-1 text-2xl font-bold text-green-700">{{ $stats['confirmed'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Checked In</p>
            <p class="mt-1 text-2xl font-bold text-red-700">{{ $stats['checked_in'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Additional Guests</p>
            <p class="mt-1 text-2xl font-bold text-blue-950">{{ $stats['total_guests'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Expected Attendance</p>
            <p class="mt-1 text-2xl font-bold text-blue-950">{{ $stats['expected_attendance'] }}</p>
            <p class="mt-1 text-xs text-slate-400">Confirmed + guests</p>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('admin.attendees.export') }}"
           class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            Export All (CSV)
        </a>
        <a href="{{ route('admin.attendees.export', ['status' => 'confirmed']) }}"
           class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            Export Confirmed (CSV)
        </a>
        <a href="{{ route('admin.attendees.export', ['status' => 'responded']) }}"
           class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            Export All Responses (CSV)
        </a>
    </div>

    <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-700">By Organization</h2>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-400">
                    <th class="px-6 py-3">Organization</th>
                    <th class="px-6 py-3">Invited</th>
                    <th class="px-6 py-3">Confirmed</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($byOrganization as $row)
                    <tr class="border-b border-slate-50">
                        <td class="px-6 py-3 font-medium text-slate-800">{{ $row->organization }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ $row->total }}</td>
                        <td class="px-6 py-3 text-green-700">{{ $row->confirmed }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center text-slate-400">No data yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
