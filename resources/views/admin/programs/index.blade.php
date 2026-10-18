@extends('admin.layout')

@section('title', 'Programs')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="max-w-2xl text-sm text-slate-500">
            Create the program for each graduation event: the Honors Ball (four generated pages),
            Baccalaureate (a generated agenda page), or Convocation (an uploaded PDF), and publish
            it to that event's page.
        </p>
        <a href="{{ route('admin.programs.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-ink px-4 py-2 text-sm font-semibold text-gold-300 transition hover:bg-neutral-800">
            + New Program
        </a>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-400">
                    <th class="px-4 py-3">Program</th>
                    <th class="px-4 py-3">Event date</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">PDF</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($programs as $program)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.programs.edit', $program) }}" class="font-semibold text-ink hover:underline">{{ $program->title }}</a>
                            <div class="text-xs text-slate-500">{{ $program->typeLabel() }} &middot; Class of {{ $program->class_year }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $program->event_date?->format('M j, Y') ?? 'Not set' }}</td>
                        <td class="px-4 py-3">
                            @include('admin.programs._status', ['program' => $program])
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            @if ($program->hasPdf())
                                {{ $program->pdf_generated_at->format('M j, g:i A') }}
                                @if ($program->pdfIsStale())
                                    <span class="ml-1 rounded bg-amber-100 px-1.5 py-0.5 text-xs font-semibold text-amber-800">Out of date</span>
                                @endif
                            @else
                                <span class="text-slate-400">Not generated</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.programs.edit', $program) }}" class="font-semibold text-slate-700 hover:text-ink">Edit</a>
                            @if ($program->hasPdf())
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('admin.programs.download', $program) }}" class="font-semibold text-slate-700 hover:text-ink">Download PDF</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-slate-500">
                            No programs yet. <a href="{{ route('admin.programs.create') }}" class="font-semibold text-ink underline">Create the first one</a> — it starts pre-filled with the Special Honors Awards agenda and award categories.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
