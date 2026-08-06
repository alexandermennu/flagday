@extends('admin.layout')

@section('title', 'Edit Attendee')

@section('content')
    <div class="max-w-2xl rounded-2xl border border-slate-200 bg-white p-8">
        <form method="POST" action="{{ route('admin.attendees.update', $attendee) }}">
            @include('admin.attendees._form')
        </form>
    </div>
@endsection
