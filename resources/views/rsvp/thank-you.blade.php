<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b0b0c">
    <title>Thank You — {{ config('event.name') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-neutral-50 font-sans text-neutral-900 antialiased">

    <x-rsvp-header :back="false" />

    <main class="mx-auto max-w-2xl px-5 py-20 text-center">
        @if ($status === 'confirmed')
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-ink text-gold-400 ring-4 ring-gold-400/30">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="mt-6 font-display text-4xl font-semibold text-ink sm:text-5xl">You're all set!</h1>
            <p class="mt-4 text-lg text-neutral-600">
                Thank you for confirming your attendance at the {{ config('event.name') }}. Check your inbox — we've
                sent your digital Event Pass with a QR check-in code.
            </p>
        @else
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-neutral-200 text-neutral-600">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <h1 class="mt-6 font-display text-4xl font-semibold text-ink sm:text-5xl">Thanks for letting us know</h1>
            <p class="mt-4 text-lg text-neutral-600">
                We're sorry you can't join us at the Honors Ball. We've noted your response — feel free to submit a
                new RSVP if your plans change.
            </p>
        @endif

        <a href="{{ route('landing') }}"
           class="mt-10 inline-flex items-center justify-center rounded-full border border-neutral-300 bg-white px-8 py-3 text-sm font-semibold text-ink transition hover:border-ink hover:bg-ink hover:text-white">
            &larr; Back to graduation events
        </a>
    </main>

</body>
</html>
