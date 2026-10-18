@extends('admin.layout')

@section('title', 'New Program')

@section('content')
    <p class="mb-6 max-w-3xl text-sm text-slate-500">
        The form starts with the Special Honors Awards agenda and award categories. Edit anything,
        add the event date, time, venue and host, then save and generate the PDF.
    </p>

    <form method="POST" action="{{ route('admin.programs.store') }}" enctype="multipart/form-data" id="program-form">
        @include('admin.programs._form')
    </form>
@endsection
