{{-- Generic "photo coming later" placeholder — used everywhere this page needs real
     photography that hasn't been supplied yet, so nothing here fakes a real image. --}}
@props(['shape' => 'rect', 'label' => 'Photo placeholder'])
@php
    $shapeClasses = $shape === 'circle' ? 'rounded-full' : 'rounded-2xl';
@endphp
<div {{ $attributes->merge(['class' => "$shapeClasses flex flex-col items-center justify-center gap-2 border-2 border-dashed border-slate-300 bg-slate-100 text-slate-400"]) }}>
    @if ($shape === 'circle')
        <svg class="h-1/3 w-1/3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 12a4.5 4.5 0 100-9 4.5 4.5 0 000 9zM12 14.25c-4.14 0-7.5 2.35-7.5 5.25v1a1 1 0 001 1h13a1 1 0 001-1v-1c0-2.9-3.36-5.25-7.5-5.25z" />
        </svg>
    @else
        <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <rect x="3" y="4" width="18" height="16" rx="2" />
            <circle cx="8.5" cy="9.5" r="1.75" />
            <path d="M21 16l-5.5-5.5a1.5 1.5 0 00-2.12 0L4 19" />
        </svg>
        @if ($label)
            <span class="text-[11px] font-semibold uppercase tracking-wider">{{ $label }}</span>
        @endif
    @endif
</div>
