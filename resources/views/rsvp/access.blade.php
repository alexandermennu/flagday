<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b0b0c">
    <title>RSVP Access | {{ config('event.name') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-ink font-sans text-neutral-900 antialiased">

    <div class="w-full max-w-sm px-5">
        <div class="mb-8 flex flex-col items-center text-center">
            <x-seal class="h-16 w-16" />
            <p class="mt-4 text-[11px] font-semibold uppercase tracking-[0.2em] text-gold-400">{{ config('graduation.institution') }} &middot; Class of {{ config('graduation.class_year') }}</p>
            <h1 class="mt-1 font-display text-2xl font-semibold text-white">Honors Ball RSVP</h1>
        </div>

        <div class="rounded-2xl bg-white p-8 shadow-xl">
            <p class="mb-6 text-center text-sm leading-relaxed text-neutral-500">
                Enter the access code from your invitation to continue to the RSVP form.
            </p>

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('rsvp.access.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="passcode" class="mb-1.5 block text-sm font-semibold text-neutral-700">Access Code</label>
                    <input type="text" name="passcode" id="passcode" required autofocus autocomplete="off"
                           autocapitalize="characters" autocorrect="off" spellcheck="false"
                           class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-center text-sm font-semibold uppercase tracking-widest focus:border-gold-500 focus:outline-none focus:ring-1 focus:ring-gold-500">
                </div>

                <button type="submit"
                        class="w-full rounded-full bg-gold-400 px-6 py-3 text-sm font-semibold text-ink shadow-sm transition hover:bg-gold-300 active:scale-[0.98]">
                    Continue
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-neutral-400">
            <a href="{{ route('landing') }}" class="hover:text-gold-300">&larr; Back to graduation events</a>
        </p>
    </div>

</body>
</html>
