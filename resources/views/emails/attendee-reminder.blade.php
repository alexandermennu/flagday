@extends('emails.layout')

@section('content')
    <h1>See you soon, {{ $attendee->full_name }}!</h1>
    <p>
        This is a friendly reminder that the {{ config('event.name') }} is coming up. We're looking forward to
        celebrating with you.
    </p>

    <table class="details">
        <tr>
            <td class="label">Confirmation ID</td>
            <td class="value">{{ $attendee->confirmation_id }}</td>
        </tr>
        <tr>
            <td class="label">Date</td>
            <td class="value">{{ \App\Support\Graduation::formatDate(config('event.date')) }}</td>
        </tr>
        <tr>
            <td class="label">Time</td>
            <td class="value">{{ \App\Support\Graduation::formatTime(config('event.start_time')) }}@if (config('event.end_time')) &ndash; {{ \App\Support\Graduation::formatTime(config('event.end_time')) }}@endif</td>
        </tr>
        <tr>
            <td class="label">Venue</td>
            <td class="value">{{ \App\Support\Graduation::orPlaceholder(config('event.venue'), config('event.venue_address')) }}</td>
        </tr>
    </table>

    <p>
        We've re-attached your Event Pass (PDF with QR code) for convenience. Please bring it, printed or on your
        phone, for entry.
        @if (\App\Support\Graduation::rsvpEventIsScheduled())
            <a href="{{ route('calendar.show', $attendee->invite_token) }}" class="calendar-link">Add to Calendar</a>
        @endif
    </p>
@endsection
