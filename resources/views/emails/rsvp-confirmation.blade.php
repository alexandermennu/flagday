@extends('emails.layout')

@section('content')
    <h1>You're confirmed, {{ $attendee->full_name }}!</h1>
    <p>
        Thank you for confirming your attendance at {{ config('event.name') }}. We're honored to have you join
        us as we raise our colors and celebrate the unity that binds our nation together.
    </p>

    <table class="details">
        <tr>
            <td class="label">Date</td>
            <td class="value">{{ date('l, F j, Y', strtotime(config('event.date'))) }}</td>
        </tr>
        <tr>
            <td class="label">Time</td>
            <td class="value">{{ date('g:i A', strtotime(config('event.start_time'))) }} &ndash; {{ date('g:i A', strtotime(config('event.end_time'))) }}</td>
        </tr>
        <tr>
            <td class="label">Venue</td>
            <td class="value">{{ config('event.venue') }}, {{ config('event.venue_address') }}</td>
        </tr>
    </table>

    <p>
        Your digital ticket is attached to this email as a PDF — it includes a QR code for check-in at the
        entrance. We've also attached a calendar file so you can add the ceremony straight to your calendar.
    </p>
    <p>Please present your ticket (printed or on your phone) when you arrive.</p>
@endsection
