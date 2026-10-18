{{-- Compact black/gold header for the RSVP flow pages. --}}
@props(['back' => true])
<header class="border-b-2 border-gold-500 bg-ink text-white">
    <div class="mx-auto flex max-w-[760px] items-center justify-between gap-4 px-5 py-4">
        <a href="{{ route('landing') }}" class="flex min-w-0 items-center gap-3">
            <x-seal class="h-10 w-10 shrink-0" />
            <span class="min-w-0 leading-tight">
                <span class="block truncate font-display text-base font-semibold text-white">{{ config('graduation.institution') }}</span>
                <span class="block text-[11px] font-semibold uppercase tracking-[0.2em] text-gold-400">Class of {{ config('graduation.class_year') }}</span>
            </span>
        </a>
        @if ($back)
            <a href="{{ route('landing') }}" class="shrink-0 text-sm font-medium text-neutral-300 transition hover:text-gold-300">&larr; <span class="hidden sm:inline">Back to </span>events</a>
        @endif
    </div>
</header>
