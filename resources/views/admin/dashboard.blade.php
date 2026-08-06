@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total</p>
            <p class="mt-1 text-2xl font-bold text-blue-950">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Confirmed</p>
            <p class="mt-1 text-2xl font-bold text-green-700">{{ $stats['confirmed'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Declined</p>
            <p class="mt-1 text-2xl font-bold text-slate-500">{{ $stats['declined'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pending</p>
            <p class="mt-1 text-2xl font-bold text-amber-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Checked In</p>
            <p class="mt-1 text-2xl font-bold text-red-700">{{ $stats['checked_in'] }}</p>
        </div>
    </div>

    <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-700">Recent Activity</h2>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-400">
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recent as $attendee)
                    <tr class="border-b border-slate-50">
                        <td class="px-6 py-3 font-medium text-slate-800">{{ $attendee->full_name }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ $attendee->email }}</td>
                        <td class="px-6 py-3"><x-status-badge :status="$attendee->status" /></td>
                        <td class="px-6 py-3 text-slate-500">{{ $attendee->updated_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-slate-400">No RSVPs yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
