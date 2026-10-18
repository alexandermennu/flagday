@extends('emails.layout')

@section('content')
    <p>Dear {{ $attendee->full_name }},</p>

    <p>
        Thank you for confirming your attendance at the {{ config('graduation.institution') }}
        Class of {{ config('graduation.class_year') }} Honors Ball.
    </p>

    <table class="details">
        <tr>
            <td class="label">Date</td>
            <td class="value">{{ \App\Support\Graduation::formatDate(config('event.date')) }}</td>
        </tr>
        <tr>
            <td class="label">Time</td>
            <td class="value">{{ \App\Support\Graduation::formatTime(config('event.start_time')) }}</td>
        </tr>
        <tr>
            <td class="label">Venue</td>
            <td class="value">{{ \App\Support\Graduation::orPlaceholder(config('event.venue'), config('event.venue_address')) }}</td>
        </tr>
    </table>

    <p>
        Your Event Pass is attached to this email. Please present it upon arrival for verification and entry.
        @if (\App\Support\Graduation::rsvpEventIsScheduled())
            <a href="{{ route('calendar.show', $attendee->invite_token) }}" class="calendar-link">Add to Calendar</a>
        @endif
    </p>

    <p>
        Any details still marked &ldquo;{{ \App\Support\Graduation::PLACEHOLDER }}&rdquo; will be published on the
        <a href="{{ route('events.show', 'honors-ball') }}" class="calendar-link">Honors Ball page</a> once confirmed.
    </p>

    <p>We look forward to celebrating the Class of {{ config('graduation.class_year') }} with you.</p>

    <p>With appreciation,</p>

    <div class="emblems">
        <img src="{{ \App\Services\IconFactory::emblemDataUri('crest') }}" alt="{{ config('graduation.institution') }}">
    </div>

    <p class="signature">
        <strong>Class of {{ config('graduation.class_year') }} Graduation Committee</strong><br>
        {{ config('graduation.institution') }}<br>
        @if (config('graduation.contact_phone'))
            Phone: {{ config('graduation.contact_phone') }}<br>
        @endif
        @if (config('graduation.contact_email'))
            Email: {{ config('graduation.contact_email') }}<br>
        @endif
    </p>
@endsection
