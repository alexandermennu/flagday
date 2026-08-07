<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Pass Verification | {{ config('event.name') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 p-6 font-sans text-slate-900 antialiased">

    <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
        <x-seal class="mx-auto h-10 w-10" />
        <p class="mt-3 text-[11px] font-semibold uppercase tracking-wider text-red-700">Republic of Liberia &middot; Ministry of Education</p>

        <div class="mx-auto mt-6 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-700">
            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <p class="mt-4 text-sm font-semibold uppercase tracking-wider text-green-700">Attendance Confirmed</p>

        <h1 class="mt-2 text-2xl font-bold text-blue-950">{{ $attendee->full_name }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $attendee->organization }}</p>
        <p class="text-sm text-slate-500">{{ $attendee->position }}</p>

        <div class="mt-5 border-t border-slate-100 pt-5">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Confirmation ID</p>
            <p class="mt-1 text-base font-bold text-red-700">{{ $attendee->confirmation_id }}</p>
        </div>

        <div class="mt-5 border-t border-slate-100 pt-5 text-sm text-slate-500">
            <p class="font-semibold text-blue-950">{{ config('event.name') }}</p>
            <p>{{ date('l, F j, Y', strtotime(config('event.date'))) }}</p>
            <p>{{ config('event.venue') }}, {{ config('event.venue_address') }}</p>
        </div>

        <a href="{{ route('landing') }}"
           class="mt-6 inline-flex items-center justify-center rounded-md border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            Back to Event Page
        </a>
    </div>

</body>
</html>
