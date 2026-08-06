<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RSVP — {{ config('event.name') }} | Republic of Liberia</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/rsvp.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">

    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-[760px] items-center justify-between px-6 py-4">
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

    <main class="mx-auto max-w-[760px] px-6 py-16">
        <div class="text-center">
            <h1 class="text-3xl font-bold tracking-tight text-blue-950 sm:text-4xl">
                {{ \Illuminate\Support\Number::ordinal(date('Y', strtotime(config('event.date'))) - 1847) }} Flag Day RSVP Form
            </h1>
            <p class="mt-4 text-slate-600">
                Kindly fill the form below to secure your spot (Note: This form is only applicable those that
                received an official invitation letter!)
            </p>
        </div>

        <div class="mt-10 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
            @if ($errors->any())
                <div class="mb-8 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <p class="font-semibold">Please correct the following:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $attendingConfirmed = old('status', 'confirmed') === 'confirmed';
                $oldGuests = old('guests', []);
                $hasGuests = count($oldGuests) > 0;
            @endphp

            <p class="mb-8 text-xs text-slate-500">Fields marked <span class="text-red-600">*</span> are required.</p>

            <form method="POST" action="{{ route('rsvp.store') }}" class="space-y-12">
                @csrf

                {{-- ============ Section: Your Information ============ --}}
                <section>
                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-blue-950">Your Information</h2>
                        <p class="mt-1 text-sm text-slate-500">Tell us who you are and where you're joining us from.</p>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">Full Name <span class="text-red-600">*</span></label>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <input type="text" name="first_name" id="first_name" required value="{{ old('first_name') }}" placeholder="First Name"
                                       class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                                <input type="text" name="last_name" id="last_name" required value="{{ old('last_name') }}" placeholder="Last Name"
                                       class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email <span class="text-red-600">*</span></label>
                            <input type="email" name="email" id="email" required value="{{ old('email') }}" placeholder="example@example.com"
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                            <p class="mt-1.5 text-xs text-slate-500">Your digital ticket and confirmation will be sent here.</p>
                        </div>

                        <div>
                            <label for="phone" class="mb-1.5 block text-sm font-semibold text-slate-700">Phone Number</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" placeholder="(000) 000-0000"
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                            <p class="mt-1.5 text-xs text-slate-500">Optional — only used if we need to reach you about the event.</p>
                        </div>

                        <div>
                            <label for="organization" class="mb-1.5 block text-sm font-semibold text-slate-700">Organization/Agency <span class="text-red-600">*</span></label>
                            <input type="text" name="organization" id="organization" required value="{{ old('organization') }}"
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                            <p class="mt-1.5 text-xs text-slate-500">The government ministry, agency, or organization you represent.</p>
                        </div>

                        <div>
                            <label for="department" class="mb-1.5 block text-sm font-semibold text-slate-700">Department/Division/Unit</label>
                            <input type="text" name="department" id="department" value="{{ old('department') }}"
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                            <p class="mt-1.5 text-xs text-slate-500">Optional — if applicable to your organization.</p>
                        </div>

                        <div>
                            <label for="position" class="mb-1.5 block text-sm font-semibold text-slate-700">Position <span class="text-red-600">*</span></label>
                            <input type="text" name="position" id="position" required value="{{ old('position') }}"
                                   class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                            <p class="mt-1.5 text-xs text-slate-500">Your official title or role.</p>
                        </div>
                    </div>
                </section>

                {{-- ============ Section: Attendance ============ --}}
                <section>
                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-blue-950">Attendance</h2>
                        <p class="mt-1 text-sm text-slate-500">Let us know if you'll be joining us.</p>
                    </div>

                    <fieldset>
                        <legend class="mb-3 text-sm font-semibold text-slate-700">Attending? <span class="text-red-600">*</span></legend>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition has-[:checked]:border-red-700 has-[:checked]:bg-red-50 has-[:checked]:text-red-700">
                                <input type="radio" name="status" value="confirmed" class="accent-red-700" {{ $attendingConfirmed ? 'checked' : '' }}>
                                Yes
                            </label>
                            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition has-[:checked]:border-slate-500 has-[:checked]:bg-slate-100">
                                <input type="radio" name="status" value="declined" class="accent-slate-600" {{ ! $attendingConfirmed ? 'checked' : '' }}>
                                No
                            </label>
                        </div>
                    </fieldset>
                </section>

                {{-- ============ Section: Additional Guests (conditional) ============ --}}
                <section id="additional-guests-section" class="{{ $attendingConfirmed ? '' : 'hidden' }}">
                    <div class="mb-6">
                        <h2 class="text-lg font-bold text-blue-950">Additional Guests</h2>
                        <p class="mt-1 text-sm text-slate-500">Bringing anyone with you? Let us know so we can prepare for them too.</p>
                    </div>

                    <fieldset>
                        <legend class="mb-3 text-sm font-semibold text-slate-700">Will you bring any additional guest(s)?</legend>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition has-[:checked]:border-red-700 has-[:checked]:bg-red-50 has-[:checked]:text-red-700">
                                <input type="radio" data-guest-toggle name="has_guests_choice" value="yes" class="accent-red-700" {{ $hasGuests ? 'checked' : '' }}>
                                Yes
                            </label>
                            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition has-[:checked]:border-slate-500 has-[:checked]:bg-slate-100">
                                <input type="radio" data-guest-toggle name="has_guests_choice" value="no" class="accent-slate-600" {{ ! $hasGuests ? 'checked' : '' }}>
                                No
                            </label>
                        </div>
                    </fieldset>

                    <div id="guest-count-field" class="mt-5 {{ $hasGuests ? '' : 'hidden' }}">
                        <label for="guest_count_select" class="mb-1.5 block text-sm font-semibold text-slate-700">How many additional guests?</label>
                        <select id="guest_count_select" class="w-full max-w-[200px] rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                            <option value="">Select&hellip;</option>
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ count($oldGuests) === $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div id="guest-cards" class="mt-5 space-y-4 {{ $hasGuests ? '' : 'hidden' }}">
                        @foreach ($oldGuests as $i => $guest)
                            <div class="guest-card rounded-xl border border-slate-200 bg-slate-50 p-5" data-guest-index="{{ $i }}">
                                <h3 class="mb-3 text-sm font-semibold text-slate-700">Guest {{ $i + 1 }}</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Full Name <span class="text-red-600">*</span></label>
                                        <input type="text" name="guests[{{ $i }}][full_name]" required value="{{ $guest['full_name'] ?? '' }}"
                                               class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Organization / Institution <span class="text-red-600">*</span></label>
                                        <input type="text" name="guests[{{ $i }}][organization]" required value="{{ $guest['organization'] ?? '' }}"
                                               class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Position / Title <span class="text-red-600">*</span></label>
                                        <input type="text" name="guests[{{ $i }}][position]" required value="{{ $guest['position'] ?? '' }}"
                                               class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <button type="submit"
                        class="w-full rounded-md bg-red-700 px-6 py-3.5 text-base font-semibold text-white shadow-lg shadow-red-700/20 transition hover:bg-red-800 active:scale-[0.98]">
                    Submit RSVP
                </button>
            </form>
        </div>
    </main>

</body>
</html>
