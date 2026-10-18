@php $in = 'rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-gold-500 focus:outline-none focus:ring-1 focus:ring-gold-500'; @endphp
<li data-repeater-row class="flex flex-col gap-2 rounded-xl border border-slate-200 bg-slate-50/60 p-2 sm:flex-row sm:items-start">
    <span data-repeater-number class="hidden w-7 shrink-0 pt-2 text-right text-xs font-semibold text-slate-400 sm:block">{{ is_int($i) ? $i + 1 : '' }}</span>
    <select name="agenda[{{ $i }}][style]" aria-label="Item style" class="{{ $in }} sm:w-56">
        @foreach (\App\Models\Program::AGENDA_STYLES as $value => $text)
            <option value="{{ $value }}" @selected(($row['style'] ?? 'item') === $value)>{{ $text }}</option>
        @endforeach
    </select>
    <div class="flex-1">
        <input type="text" name="agenda[{{ $i }}][title]" value="{{ $row['title'] ?? '' }}" maxlength="80" aria-label="Agenda item" placeholder="Agenda item"
               class="{{ $in }} w-full">
        @if (is_int($i)) @error("agenda.$i.title") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror @endif
    </div>
    @include('admin.programs._row-controls')
</li>
