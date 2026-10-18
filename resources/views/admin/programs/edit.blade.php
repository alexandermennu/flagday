@extends('admin.layout')

@section('title', 'Edit Program')

@section('content')
    {{-- PDF actions --}}
    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status</div>
                    <div class="mt-1">@include('admin.programs._status', ['program' => $program])</div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Generated PDF</div>
                    <div class="mt-1 text-slate-700">
                        @if ($program->hasPdf())
                            {{ $program->pdf_generated_at->format('M j, Y g:i A') }} &middot; {{ $program->pdf_page_count }} pages &middot; {{ number_format($program->pdf_size / 1024) }} KB
                        @else
                            <span class="text-slate-400">Not generated yet</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.programs.preview', $program) }}" target="_blank" rel="noopener"
                   class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Preview
                </a>
                <form method="POST" action="{{ route('admin.programs.generate', $program) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        {{ $program->hasPdf() ? 'Regenerate PDF' : 'Generate PDF' }}
                    </button>
                </form>
                @if ($program->hasPdf())
                    <a href="{{ route('admin.programs.download', $program) }}"
                       class="inline-flex items-center rounded-lg bg-ink px-4 py-2 text-sm font-semibold text-gold-300 transition hover:bg-neutral-800">
                        Download PDF
                    </a>
                @endif
                <form method="POST" action="{{ route('admin.programs.publish', $program) }}"
                      onsubmit="return confirm('Publish this program as version {{ $program->published_version + 1 }}? The PDF will be regenerated from the saved content.')">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-lg bg-gold-400 px-4 py-2 text-sm font-semibold text-ink transition hover:bg-gold-300">
                        {{ $program->published_at ? 'Publish update' : 'Publish' }}
                    </button>
                </form>
            </div>
        </div>

        @if ($program->pdfIsStale())
            <p class="mt-4 rounded-lg bg-amber-50 px-4 py-2.5 text-sm text-amber-800">
                The program was edited after this PDF was generated. Regenerate it (or publish an update) so the download matches the saved content.
            </p>
        @endif
        <p class="mt-3 text-xs text-slate-500">Preview and the generated PDF use the <strong>saved</strong> content — save your changes first.</p>
    </div>

    <form method="POST" action="{{ route('admin.programs.update', $program) }}" enctype="multipart/form-data" id="program-form">
        @method('PUT')
        @include('admin.programs._form')
    </form>

    <form method="POST" action="{{ route('admin.programs.destroy', $program) }}" class="mt-10 border-t border-slate-200 pt-6"
          onsubmit="return confirm('Delete this program and its generated PDF? This cannot be undone.')">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-sm font-semibold text-red-700 hover:text-red-800">Delete program</button>
    </form>
@endsection
