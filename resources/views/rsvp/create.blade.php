<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RSVP — {{ config('event.name') }} | Republic of Liberia</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">

    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-3xl items-center justify-between px-6 py-4">
            <a href="{{ route('landing') }}" class="flex items-center gap-3">
                <x-seal class="h-9 w-9 shrink-0" />
                <span class="leading-tight">
                    <span class="block text-[11px] font-semibold uppercase tracking-wider text-red-700">Republic of Liberia</span>
                    <span class="block text-sm font-bold text-blue-950">Ministry of Education</span>
                </span>
            </a>
            <a href="{{ route('landing') }}" class="text-sm font-medium text-slate-500 transition hover:text-blue-950">&larr; Back to event page</a>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-6 py-16">
        <div class="text-center">
            <span class="text-sm font-semibold uppercase tracking-wider text-red-700">RSVP</span>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-blue-950 sm:text-4xl">
                {{ config('event.name') }}
            </h1>
            <p class="mt-4 text-slate-600">
                {{ date('l, F j, Y', strtotime(config('event.date'))) }} &middot;
                {{ config('event.venue') }}, {{ config('event.venue_address') }}
            </p>
        </div>

        <div class="mt-10 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <p class="font-semibold">Please correct the following:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('rsvp.store') }}" class="space-y-6">
                @csrf

                <fieldset>
                    <legend class="mb-3 text-sm font-semibold text-slate-700">Will you be attending?</legend>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition has-[:checked]:border-red-700 has-[:checked]:bg-red-50 has-[:checked]:text-red-700">
                            <input type="radio" name="status" value="confirmed" class="accent-red-700" {{ old('status', 'confirmed') === 'confirmed' ? 'checked' : '' }}>
                            Yes, I'll attend
                        </label>
                        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition has-[:checked]:border-slate-500 has-[:checked]:bg-slate-100">
                            <input type="radio" name="status" value="declined" class="accent-slate-600" {{ old('status') === 'declined' ? 'checked' : '' }}>
                            No, I can't make it
                        </label>
                    </div>
                </fieldset>

                <div>
                    <label for="full_name" class="mb-1.5 block text-sm font-semibold text-slate-700">Full name</label>
                    <input type="text" name="full_name" id="full_name" required value="{{ old('full_name') }}"
                           class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                </div>

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email address</label>
                    <input type="email" name="email" id="email" required value="{{ old('email') }}"
                           class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                    <p class="mt-1.5 text-xs text-slate-500">Your digital ticket and confirmation will be sent here.</p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="phone" class="mb-1.5 block text-sm font-semibold text-slate-700">Phone <span class="font-normal text-slate-400">(optional)</span></label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                               class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                    </div>
                    <div>
                        <label for="organization" class="mb-1.5 block text-sm font-semibold text-slate-700">Organization <span class="font-normal text-slate-400">(optional)</span></label>
                        <input type="text" name="organization" id="organization" value="{{ old('organization') }}"
                               class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                    </div>
                </div>

                <button type="submit"
                        class="w-full rounded-md bg-red-700 px-6 py-3.5 text-base font-semibold text-white shadow-lg shadow-red-700/20 transition hover:bg-red-800 active:scale-[0.98]">
                    Submit RSVP
                </button>
            </form>
        </div>
    </main>

</body>
</html>
