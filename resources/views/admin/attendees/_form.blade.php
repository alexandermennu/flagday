@csrf
@if (isset($attendee))
    @method('PUT')
@endif

<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <label for="full_name" class="mb-1.5 block text-sm font-semibold text-slate-700">Full name</label>
        <input type="text" name="full_name" id="full_name" required value="{{ old('full_name', $attendee->full_name ?? '') }}"
               class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
        @error('full_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label>
        <input type="email" name="email" id="email" required value="{{ old('email', $attendee->email ?? '') }}"
               class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="phone" class="mb-1.5 block text-sm font-semibold text-slate-700">Phone</label>
        <input type="tel" name="phone" id="phone" value="{{ old('phone', $attendee->phone ?? '') }}"
               class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
    </div>

    <div>
        <label for="organization" class="mb-1.5 block text-sm font-semibold text-slate-700">Organization</label>
        <input type="text" name="organization" id="organization" value="{{ old('organization', $attendee->organization ?? '') }}"
               class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
    </div>

    <div>
        <label for="status" class="mb-1.5 block text-sm font-semibold text-slate-700">Status</label>
        <select name="status" id="status"
                class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
            @foreach (\App\Enums\AttendeeStatus::cases() as $case)
                <option value="{{ $case->value }}" @selected(old('status', $attendee->status->value ?? 'pending') === $case->value)>{{ $case->label() }}</option>
            @endforeach
        </select>
        @if (isset($attendee))
            <p class="mt-1.5 text-xs text-slate-500">Setting this to Confirmed sends them a fresh digital ticket by email.</p>
        @endif
    </div>
</div>

<div class="mt-8 flex items-center gap-3">
    <button type="submit" class="rounded-md bg-red-700 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-red-800 active:scale-[0.98]">
        {{ isset($attendee) ? 'Save Changes' : 'Add Attendee' }}
    </button>
    <a href="{{ route('admin.attendees.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Cancel</a>
</div>
