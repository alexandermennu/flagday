<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b0b0c">
    <title>RSVP Closed — {{ config('event.name') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-neutral-50 font-sans text-neutral-900 antialiased">

    <x-rsvp-header />

    <main class="mx-auto max-w-2xl px-5 py-20 text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-ink text-gold-400 ring-4 ring-gold-400/30">
            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>
        <p class="mt-6 text-xs font-semibold uppercase tracking-[0.3em] text-gold-600">RSVP &middot; Honors Ball</p>
        <h1 class="mt-3 font-display text-4xl font-semibold text-ink sm:text-5xl">RSVP is closed</h1>
        <p class="mt-4 text-lg text-neutral-600">
            The RSVP for the {{ config('event.name') }} is no longer accepting responses. If you already confirmed,
            your Event Pass in your inbox is still valid.
        </p>

        <a href="{{ route('landing') }}"
           class="mt-10 inline-flex items-center justify-center rounded-full border border-neutral-300 bg-white px-8 py-3 text-sm font-semibold text-ink transition hover:border-ink hover:bg-ink hover:text-white">
            &larr; Back to graduation events
        </a>
    </main>

</body>
</html>
