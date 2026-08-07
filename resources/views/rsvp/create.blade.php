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
                $oldStatus = old('status');
                $showYes = $oldStatus === 'confirmed';
                $showNo = $oldStatus === 'declined';
                $oldGuests = old('guests', []);
                $hasGuests = count($oldGuests) > 0;

                $inputClass = 'w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950';
            @endphp

            <form method="POST" action="{{ route('rsvp.store') }}" id="rsvp-form" class="space-y-10">
                @csrf

                {{-- ============ Step 1: Attendance ============ --}}
                <section class="text-center">
                    <h2 class="text-xl font-bold text-blue-950">Will you attend the National Flag Day Celebration?</h2>
                    <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border-2 border-[#1b2652] bg-[#1b2652] px-5 py-2.5 text-sm font-semibold text-white transition has-[:checked]:ring-2 has-[:checked]:ring-[#1b2652] has-[:checked]:ring-offset-2">
                            <input type="radio" name="status" id="attendance-yes" value="confirmed" class="sr-only" {{ $showYes ? 'checked' : '' }}>
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                            </svg>
                            Yes, I will attend
                        </label>
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border-2 border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition has-[:checked]:border-slate-500 has-[:checked]:bg-slate-100">
                            <input type="radio" name="status" id="attendance-no" value="declined" class="sr-only" {{ $showNo ? 'checked' : '' }}>
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" clip-rule="evenodd" />
                            </svg>
                            No, I am unable to attend
                        </label>
                    </div>
                </section>

                {{-- ============ Yes path ============ --}}
                <div id="attend-yes-block" class="{{ $showYes ? '' : 'hidden' }} space-y-10">
                    <section>
                        <div class="mb-6">
                            <h2 class="text-lg font-bold text-blue-950">Your Information</h2>
                            <p class="mt-1 text-sm text-slate-500">Tell us who you are and where you're joining us from.</p>
                        </div>

                        <div class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
                            <div>
                                <label for="first_name" class="mb-1.5 block text-sm font-semibold text-slate-700">First Name <span class="text-red-600">*</span></label>
                                <input type="text" name="first_name" id="first_name" data-sync-field="first_name" value="{{ old('first_name') }}" class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label for="last_name" class="mb-1.5 block text-sm font-semibold text-slate-700">Last Name <span class="text-red-600">*</span></label>
                                <input type="text" name="last_name" id="last_name" data-sync-field="last_name" value="{{ old('last_name') }}" class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email <span class="text-red-600">*</span></label>
                                <input type="email" name="email" id="email" data-sync-field="email" value="{{ old('email') }}" placeholder="example@example.com" class="{{ $inputClass }}">
                                <p class="mt-1.5 text-xs text-slate-500">Your digital ticket and confirmation will be sent here.</p>
                            </div>
                            <div>
                                <label for="phone" class="mb-1.5 block text-sm font-semibold text-slate-700">Phone Number</label>
                                <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" placeholder="(000) 000-0000" class="{{ $inputClass }}">
                            </div>

                            <div>
                                <label for="organization" class="mb-1.5 block text-sm font-semibold text-slate-700">Organization/Agency <span class="text-red-600">*</span></label>
                                <input type="text" name="organization" id="organization" data-sync-field="organization" value="{{ old('organization') }}" class="{{ $inputClass }}">
                            </div>
                            <div>
                                <label for="department" class="mb-1.5 block text-sm font-semibold text-slate-700">Department/Division/Unit</label>
                                <input type="text" name="department" id="department" value="{{ old('department') }}" class="{{ $inputClass }}">
                            </div>

                            <div class="sm:col-span-2">
                                <label for="position" class="mb-1.5 block text-sm font-semibold text-slate-700">Position <span class="text-red-600">*</span></label>
                                <input type="text" name="position" id="position" value="{{ old('position') }}" class="{{ $inputClass }}">
                                <p class="mt-1.5 text-xs text-slate-500">Your official title or role.</p>
                            </div>
                        </div>
                    </section>

                    <section id="additional-guests-section">
                        <div class="mb-6">
                            <h2 class="text-lg font-bold text-blue-950">Additional Guests</h2>
                            <p class="mt-1 text-sm text-slate-500">Bringing anyone with you? Let us know so we can prepare for them too.</p>
                        </div>

                        <p class="mb-3 text-sm font-semibold text-slate-700">Will you bring additional guest(s)?</p>
                        <div class="flex flex-wrap gap-3">
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border-2 border-slate-300 px-5 py-2 text-sm font-semibold text-slate-700 transition has-[:checked]:border-red-700 has-[:checked]:bg-red-50 has-[:checked]:text-red-700">
                                <input type="radio" data-guest-toggle name="has_guests_choice" value="yes" class="sr-only" {{ $hasGuests ? 'checked' : '' }}>
                                Yes
                            </label>
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border-2 border-slate-300 px-5 py-2 text-sm font-semibold text-slate-700 transition has-[:checked]:border-slate-500 has-[:checked]:bg-slate-100">
                                <input type="radio" data-guest-toggle name="has_guests_choice" value="no" class="sr-only" {{ ! $hasGuests ? 'checked' : '' }}>
                                No
                            </label>
                        </div>

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
                                            <input type="text" name="guests[{{ $i }}][full_name]" value="{{ $guest['full_name'] ?? '' }}" class="{{ $inputClass }} bg-white">
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Organization <span class="text-red-600">*</span></label>
                                            <input type="text" name="guests[{{ $i }}][organization]" value="{{ $guest['organization'] ?? '' }}" class="{{ $inputClass }} bg-white">
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Position <span class="text-red-600">*</span></label>
                                            <input type="text" name="guests[{{ $i }}][position]" value="{{ $guest['position'] ?? '' }}" class="{{ $inputClass }} bg-white">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>

                {{-- ============ No path ============ --}}
                <div id="attend-no-block" class="{{ $showNo ? '' : 'hidden' }} space-y-6">
                    <div class="mb-2">
                        <h2 class="text-lg font-bold text-blue-950">A Few Details</h2>
                        <p class="mt-1 text-sm text-slate-500">We're sorry you can't join us — just a few details so we can keep our records accurate.</p>
                    </div>

                    <div class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
                        <div>
                            <label for="first_name_no" class="mb-1.5 block text-sm font-semibold text-slate-700">First Name <span class="text-red-600">*</span></label>
                            <input type="text" name="first_name" id="first_name_no" data-sync-field="first_name" value="{{ old('first_name') }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label for="last_name_no" class="mb-1.5 block text-sm font-semibold text-slate-700">Last Name <span class="text-red-600">*</span></label>
                            <input type="text" name="last_name" id="last_name_no" data-sync-field="last_name" value="{{ old('last_name') }}" class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label for="email_no" class="mb-1.5 block text-sm font-semibold text-slate-700">Email <span class="text-red-600">*</span></label>
                            <input type="email" name="email" id="email_no" data-sync-field="email" value="{{ old('email') }}" placeholder="example@example.com" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label for="organization_no" class="mb-1.5 block text-sm font-semibold text-slate-700">Organization/Agency <span class="text-red-600">*</span></label>
                            <input type="text" name="organization" id="organization_no" data-sync-field="organization" value="{{ old('organization') }}" class="{{ $inputClass }}">
                        </div>
                    </div>

                    <div>
                        <label for="decline_reason" class="mb-1.5 block text-sm font-semibold text-slate-700">Reason for declining <span class="font-normal text-slate-400">(optional)</span></label>
                        <textarea name="decline_reason" id="decline_reason" rows="3" class="{{ $inputClass }}">{{ old('decline_reason') }}</textarea>
                    </div>
                </div>

                <button type="submit" id="submit-button"
                        class="{{ ($showYes || $showNo) ? '' : 'hidden' }} w-full rounded-md bg-red-700 px-6 py-3.5 text-base font-semibold text-white shadow-lg shadow-red-700/20 transition hover:bg-red-800 active:scale-[0.98]">
                    Submit RSVP
                </button>

                <div class="flex items-center justify-center gap-2 rounded-lg bg-red-50 px-4 py-3 text-center text-sm text-slate-500">
                    <svg class="h-4 w-4 shrink-0 text-red-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                    </svg>
                    Your response is secure and will only be used for event planning purposes.
                </div>
            </form>
        </div>
    </main>

</body>
</html>
