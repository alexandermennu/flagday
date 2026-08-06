<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Check-In | {{ config('event.name') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 p-6 font-sans text-slate-900 antialiased">

    <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
        @if ($alreadyCheckedIn)
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="mt-4 text-sm font-semibold uppercase tracking-wider text-amber-700">Already Checked In</p>
        @else
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-700">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="mt-4 text-sm font-semibold uppercase tracking-wider text-green-700">Checked In</p>
        @endif

        <h1 class="mt-2 text-2xl font-bold text-blue-950">{{ $attendee->full_name }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $attendee->email }}</p>

        <div class="mt-4">
            <x-status-badge :status="$attendee->status" />
        </div>

        <p class="mt-4 text-xs text-slate-400">
            Checked in {{ $attendee->checked_in_at->format('M j, Y \a\t g:i A') }}
        </p>

        <a href="{{ route('admin.attendees.index') }}"
           class="mt-6 inline-flex items-center justify-center rounded-md border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            Back to Attendees
        </a>
    </div>

</body>
</html>
