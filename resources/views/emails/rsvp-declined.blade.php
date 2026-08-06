@extends('emails.layout')

@section('content')
    <h1>Thanks for letting us know, {{ $attendee->full_name }}</h1>
    <p>
        We've recorded that you won't be able to join us for {{ config('event.name') }} this year. We're sorry
        to miss you, and we hope to see you at a future celebration.
    </p>
    <p>
        If your plans change, you're welcome to submit a new response any time before the event.
    </p>
@endsection
