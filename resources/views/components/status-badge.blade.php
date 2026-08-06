@props(['status'])
@php
    $classes = match ($status) {
        \App\Enums\AttendeeStatus::Confirmed => 'bg-green-50 text-green-700 border-green-200',
        \App\Enums\AttendeeStatus::Declined => 'bg-slate-100 text-slate-500 border-slate-200',
        \App\Enums\AttendeeStatus::Pending => 'bg-amber-50 text-amber-700 border-amber-200',
    };
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold $classes"]) }}>
    {{ $status->label() }}
</span>
