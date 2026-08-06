@extends('emails.layout')

@section('content')
    <h1>See you soon, {{ $attendee->full_name }}!</h1>
    <p>
        This is a friendly reminder that {{ config('event.name') }} is coming up. We're looking forward to
        celebrating with you.
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
        We've re-attached your digital ticket (PDF with QR code) and a calendar file for convenience.
        Please bring your ticket, printed or on your phone, for check-in at the entrance.
    </p>
@endsection
