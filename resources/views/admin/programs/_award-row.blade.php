@php $in = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-gold-500 focus:outline-none focus:ring-1 focus:ring-gold-500'; @endphp
<li data-repeater-row class="flex gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-3">
    <span data-repeater-number class="w-6 shrink-0 pt-2 text-right text-xs font-semibold text-slate-400">{{ is_int($i) ? $i + 1 : '' }}</span>
    <div class="flex-1 space-y-2">
        <input type="text" name="awards[{{ $i }}][name]" value="{{ $row['name'] ?? '' }}" maxlength="80" aria-label="Award name" placeholder="Award name"
               class="{{ $in }} font-semibold">
        @if (is_int($i)) @error("awards.$i.name") <p class="text-xs text-red-600">{{ $message }}</p> @enderror @endif
        <textarea name="awards[{{ $i }}][description]" rows="3" maxlength="1500" aria-label="Award description" placeholder="Description"
                  class="{{ $in }}">{{ $row['description'] ?? '' }}</textarea>
        @if (is_int($i)) @error("awards.$i.description") <p class="text-xs text-red-600">{{ $message }}</p> @enderror @endif
    </div>
    @include('admin.programs._row-controls')
</li>
