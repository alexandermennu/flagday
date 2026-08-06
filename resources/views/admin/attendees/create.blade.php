@extends('admin.layout')

@section('title', 'Add Attendee')

@section('content')
    <div class="max-w-2xl rounded-2xl border border-slate-200 bg-white p-8">
        <form method="POST" action="{{ route('admin.attendees.store') }}">
            @include('admin.attendees._form')
        </form>
    </div>
@endsection
