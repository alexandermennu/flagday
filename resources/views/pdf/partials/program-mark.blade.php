{{-- The "Special Honors Awards" wordmark: small caps lines either side of a large script word. --}}
@if ($p->mark_top || $p->mark_script || $p->mark_bottom)
    <div class="mark" style="{{ $style }}">
        <div class="small" style="top: 14pt; left: 60pt; font-size: {{ $L['markSmallFs'] }}pt">{{ mb_strtoupper((string) $p->mark_top) }}</div>
        <div class="script" style="color: {{ $scriptColor }}; font-size: {{ $L['markScriptFs'] }}pt">{{ $p->mark_script }}</div>
        <div class="small" style="top: 66pt; left: 60pt; font-size: {{ $L['markSmallFs'] }}pt">{{ mb_strtoupper((string) $p->mark_bottom) }}</div>
    </div>
@endif
