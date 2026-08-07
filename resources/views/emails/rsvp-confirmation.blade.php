@extends('emails.layout')

@php
    $edition = \Illuminate\Support\Number::ordinal(date('Y', strtotime(config('event.date'))) - 1847);
@endphp

@section('content')
    <p>Dear {{ $attendee->full_name }},</p>

    <p>Thank you for confirming your attendance at the {{ $edition }} {{ config('event.name') }} Celebration.</p>

    <p>
        We sincerely appreciate your response and look forward to welcoming you as we commemorate this important
        national occasion under the theme:
    </p>

    <p><em>&ldquo;{{ config('event.theme') }}&rdquo;</em></p>

    <p>
        Your official Event Pass is attached to this email. Please present it upon arrival for verification and
        entry. <a href="{{ route('calendar.show', $attendee->invite_token) }}" class="calendar-link">Add to Calendar</a>
    </p>

    <p>Your presence is greatly valued, and we look forward to celebrating this historic national occasion with you.</p>

    <p>With appreciation,</p>

    <div class="emblems">
        <img src="{{ \App\Services\IconFactory::emblemDataUri('seal') }}" alt="Seal of the Republic of Liberia">
        <img src="{{ \App\Services\IconFactory::emblemDataUri('moe') }}" alt="Ministry of Education">
    </div>
    <p class="emblem-captions"><span>Republic of Liberia</span><span>Ministry of Education</span></p>

    <p class="signature">
        <strong>{{ $edition }} National Flag Day Planning Committee</strong><br>
        Office of the Assistant Minister<br>
        Bureau of Student Personnel Services<br>
        Ministry of Education, Republic of Liberia<br>
        Phone: {{ config('event.contact_phone') }} / {{ config('event.contact_phone_secondary') }}<br>
        WhatsApp: {{ config('event.contact_whatsapp') }}<br>
        Website: {{ config('event.contact_website') }}
    </p>
@endsection
