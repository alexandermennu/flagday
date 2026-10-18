@if ($program->published_at)
    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">Published v{{ $program->published_version }}</span>
    <div class="mt-0.5 text-xs text-slate-500">{{ $program->published_at->format('M j, Y g:i A') }}</div>
@else
    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">Draft</span>
@endif
