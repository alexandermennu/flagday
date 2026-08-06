<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thank You — {{ config('event.name') }} | Republic of Liberia</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">

    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-3xl items-center px-6 py-4">
            <a href="{{ route('landing') }}" class="flex items-center gap-3">
                <x-seal class="h-9 w-9 shrink-0" />
                <span class="leading-tight">
                    <span class="block text-[11px] font-semibold uppercase tracking-wider text-red-700">Republic of Liberia</span>
                    <span class="block text-sm font-bold text-blue-950">Ministry of Education</span>
                </span>
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-2xl px-6 py-20 text-center">
        @if ($status === 'confirmed')
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-700">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="mt-6 text-3xl font-bold tracking-tight text-blue-950 sm:text-4xl">You're all set!</h1>
            <p class="mt-4 text-lg text-slate-600">
                Thank you for confirming your attendance at {{ config('event.name') }}. Check your inbox — we've
                sent your digital ticket (with QR check-in code) and a calendar invite.
            </p>
        @else
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-200 text-slate-600">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <h1 class="mt-6 text-3xl font-bold tracking-tight text-blue-950 sm:text-4xl">Thanks for letting us know</h1>
            <p class="mt-4 text-lg text-slate-600">
                We're sorry you can't join us this time. We've noted your response — feel free to submit a new
                RSVP if your plans change.
            </p>
        @endif

        <a href="{{ route('landing') }}"
           class="mt-10 inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-8 py-3 text-sm font-semibold text-slate-800 transition hover:border-slate-400 hover:bg-slate-50">
            &larr; Back to event page
        </a>
    </main>

</body>
</html>
