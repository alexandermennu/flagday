<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RSVP Access | {{ config('event.name') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 font-sans text-slate-900 antialiased">

    <div class="w-full max-w-sm px-6">
        <div class="mb-8 flex flex-col items-center text-center">
            <x-seal class="h-14 w-14" />
            <p class="mt-4 text-[11px] font-semibold uppercase tracking-wider text-red-700">Republic of Liberia</p>
            <h1 class="text-lg font-bold text-blue-950">RSVP Access</h1>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <p class="mb-6 text-center text-sm leading-relaxed text-slate-500">
                This RSVP form is available to invited guests only. Enter the access code
                from your invitation to continue.
            </p>

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('rsvp.access.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="passcode" class="mb-1.5 block text-sm font-semibold text-slate-700">Access Code</label>
                    <input type="text" name="passcode" id="passcode" required autofocus autocomplete="off"
                           autocapitalize="characters" autocorrect="off" spellcheck="false"
                           class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold uppercase tracking-widest focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                </div>

                <button type="submit"
                        class="w-full rounded-md bg-red-700 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-red-800 active:scale-[0.98]">
                    Continue
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
            <a href="{{ route('landing') }}" class="hover:text-slate-600">&larr; Back to event page</a>
        </p>
    </div>

</body>
</html>
