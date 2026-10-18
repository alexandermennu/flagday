{{--
    Decorative celebration layer for an event hero: floating graduation caps, twinkling gold
    sparkles and occasional small fireworks (styles: .hero-fx in resources/css/app.css).
    Sits behind the content, ignores the pointer, and is hidden from assistive technology.

    Placement keeps the text clear: on large screens the hero copy fills the left ~60%, so
    caps and fireworks stay in the right-hand area (and, on extra-wide screens, the outer
    gutters). Below lg the copy runs full width, so only a couple of small caps and one
    firework appear, tucked into the empty top-right corner above the heading.
--}}
@php
    // [right %, top %, size px, tilt deg, duration s, delay s, opacity, visibility classes]
    $caps = [
        [8, 14, 64, -8, 7, 0, 0.85, 'hidden lg:block'],
        [23, 32, 40, 10, 8.5, -2, 0.55, 'hidden lg:block'],
        [5, 56, 50, 6, 6.5, -4, 0.7, 'hidden lg:block'],
        [17, 74, 30, -12, 9, -1, 0.45, 'hidden lg:block'],
        [27, 9, 26, 14, 7.5, -3, 0.4, 'hidden xl:block'],
        [5, 5, 30, -10, 7, 0, 0.7, 'lg:hidden'],
        [17, 10, 20, 12, 8, -2.5, 0.5, 'lg:hidden'],
    ];

    // [left %, top %, size px, duration s, delay s, peak opacity, star?, visibility classes]
    // Brighter sparkles sit on the right; the few over the text side are tiny and faint.
    // Visibility follows measured clearance from the text and RSVP button: corner and
    // edge sparkles show everywhere, the right-hand cluster from lg, and the ones that
    // would touch text at 1024px only from xl.
    $sparks = [
        [62, 18, 3, 5.5, 0, 0.75, false, 'hidden lg:block'], [70, 42, 4, 6.5, -1.2, 0.8, true, 'hidden lg:block'],
        [78, 24, 2, 4.8, -2.4, 0.7, false, 'hidden lg:block'], [84, 66, 3, 6, -0.6, 0.75, false, 'hidden lg:block'],
        [91, 34, 5, 7, -3.1, 0.85, true, ''], [74, 82, 2, 5.2, -1.8, 0.6, false, 'hidden lg:block'],
        [95, 76, 3, 6.4, -4, 0.7, false, ''], [66, 58, 2, 5.8, -2.9, 0.55, false, 'hidden xl:block'],
        [88, 12, 4, 6.8, -0.3, 0.8, true, ''], [81, 50, 2, 4.6, -3.6, 0.6, false, 'hidden lg:block'],
        [58, 88, 3, 6.2, -1, 0.5, false, 'hidden lg:block'], [97, 52, 2, 5, -2.2, 0.6, false, ''],
        [72, 8, 3, 7.2, -4.4, 0.65, true, ''], [86, 90, 2, 5.6, -0.9, 0.55, false, 'hidden lg:block'],
        [4, 30, 2, 6.6, -1.5, 0.35, false, 'hidden xl:block'], [12, 84, 2, 7.4, -3.3, 0.3, false, 'hidden xl:block'],
        [30, 92, 2, 6, -2.6, 0.3, false, 'hidden lg:block'], [46, 6, 2, 5.4, -0.4, 0.3, false, ''],
        [2, 62, 3, 7, -4.8, 0.35, true, 'hidden lg:block'], [38, 70, 2, 6.8, -5.2, 0.25, false, 'hidden lg:block'],
        // Small screens only: the empty strip above the back link.
        [64, 2, 2, 6, -1.4, 0.6, false, 'lg:hidden'], [82, 3, 3, 6.6, -3.8, 0.7, true, 'lg:hidden'],
    ];

    // [css position, burst radius px, cycle s, delay s, visibility classes]
    $fireworks = [
        ['right: 4%; top: 13%', 28, 11, 1.5, 'hidden lg:block'],
        ['right: 13%; bottom: 13%', 22, 13, 6, 'hidden lg:block'],
        ['left: 3%; top: 22%', 20, 12, 3.5, 'hidden xl:block'],
        ['left: 4%; bottom: 18%', 18, 14, 9, 'hidden xl:block'],
        ['right: 9%; top: 6%', 16, 12, 2, 'lg:hidden'],
    ];
@endphp

<div class="hero-fx" aria-hidden="true">
    @foreach ($sparks as [$left, $top, $size, $duration, $delay, $peak, $star, $visibility])
        <span class="hero-fx__spark{{ $star ? ' hero-fx__spark--star' : '' }} {{ $visibility }}"
              style="left: {{ $left }}%; top: {{ $top }}%; width: {{ $star ? $size * 2 : $size }}px; height: {{ $star ? $size * 2 : $size }}px; --duration: {{ $duration }}s; --delay: {{ $delay }}s; --peak: {{ $peak }}"></span>
    @endforeach

    @foreach ($fireworks as [$position, $radius, $cycle, $delay, $visibility])
        <span class="hero-fx__firework {{ $visibility }}" style="{{ $position }}">
            @for ($angle = 0; $angle < 360; $angle += 30)
                <span class="hero-fx__ray" style="--angle: {{ $angle }}deg; --radius: {{ $radius }}px; --cycle: {{ $cycle }}s; --delay: {{ $delay }}s"></span>
            @endfor
        </span>
    @endforeach

    @foreach ($caps as [$right, $top, $size, $tilt, $duration, $delay, $opacity, $visibility])
        <svg class="hero-fx__cap {{ $visibility }}" viewBox="0 0 64 48" width="{{ $size }}" height="{{ round($size * 0.75) }}"
             style="right: {{ $right }}%; top: {{ $top }}%; opacity: {{ $opacity }}; --tilt: {{ $tilt }}deg; --duration: {{ $duration }}s; --delay: {{ $delay }}s">
            {{-- Mortarboard: board, cap band, and tassel — gold on the dark hero. --}}
            <path d="M32 4 62 15 32 26 2 15Z" fill="currentColor" />
            <path d="M14 20.5v10c0 4.5 8 8 18 8s18-3.5 18-8v-10L32 27Z" fill="currentColor" opacity=".78" />
            <path d="M32 15 51 21v11" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
            <circle cx="51" cy="34" r="2.6" fill="currentColor" />
        </svg>
    @endforeach
</div>
