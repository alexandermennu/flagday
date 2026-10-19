{{--
    Decorative celebration layer for an event hero: floating graduation caps, twinkling gold
    sparkles and occasional small fireworks (styles: .hero-fx in resources/css/app.css).
    Sits behind the content, ignores the pointer, and is hidden from assistive technology.

    Pass `layout` to match the hero:
    - 'aside' (default, event pages): the copy fills the left ~60% on large screens, so caps
      and fireworks stay in the right-hand area (and, on extra-wide screens, the outer
      gutters). Below lg the copy runs full width, so only a couple of small caps and one
      firework appear, tucked into the empty top-right corner above the heading.
    - 'aside-card' (Honors Ball, with its program card in the hero's right column): the
      decorations sit in the bands above and below the card and in the outer gutters; on small
      screens (card stacked below the copy) they keep to the strip above the back link.
    - 'centered' (home page): the copy is centred, so the decorations frame it from the
      outer left and right edges; on small screens they sit in the top corners beside the logo.
    Positions were checked in the browser against the text and buttons at 360–1920px wide.
--}}
@php
    $layout ??= 'aside';

    // [css position, size px, tilt deg, duration s, delay s, opacity, visibility classes]
    $caps = [
        ['right: 8%; top: 14%', 64, -8, 7, 0, 0.85, 'hidden lg:block'],
        ['right: 23%; top: 32%', 40, 10, 8.5, -2, 0.55, 'hidden lg:block'],
        ['right: 5%; top: 56%', 50, 6, 6.5, -4, 0.7, 'hidden lg:block'],
        ['right: 5%; top: 5%', 30, -10, 7, 0, 0.7, 'lg:hidden'],
        ['right: 17%; top: 10%', 20, 12, 8, -2.5, 0.5, 'lg:hidden'],
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

    if ($layout === 'aside-card') {
        // The card fills roughly 62–83% across and 23–66% down on large screens.
        $caps = [
            ['right: 6%; top: 4%', 48, -8, 7, 0, 0.85, 'hidden lg:block'],
            ['right: 24%; top: 8%', 30, 12, 8.5, -2, 0.55, 'hidden lg:block'],
            ['right: 10%; top: 78%', 40, 6, 6.5, -4, 0.7, 'hidden lg:block'],
            // Small screens: pinned in px to the strip above the back link (the stacked hero is tall).
            ['right: 5%; top: 14px', 30, -10, 7, 0, 0.7, 'lg:hidden'],
            ['right: 17%; top: 40px', 20, 12, 8, -2.5, 0.5, 'lg:hidden'],
        ];

        $sparks = [
            [60, 6, 3, 5.5, 0, 0.7, false, 'hidden lg:block'], [70, 14, 4, 6.5, -1.2, 0.8, true, 'hidden lg:block'],
            [86, 3, 2, 4.8, -2.4, 0.6, false, 'hidden lg:block'], [92, 16, 3, 6, -0.6, 0.7, false, 'hidden lg:block'],
            [66, 76, 2, 5.2, -1.8, 0.6, false, 'hidden lg:block'], [80, 92, 3, 6.2, -1, 0.65, true, 'hidden lg:block'],
            [94, 86, 2, 5.6, -0.9, 0.55, false, 'hidden lg:block'], [72, 88, 2, 6.8, -3.6, 0.5, false, 'hidden lg:block'],
            [97, 40, 3, 7, -3.1, 0.75, true, 'hidden xl:block'], [96, 58, 2, 5, -2.2, 0.6, false, 'hidden xl:block'],
            [4, 30, 2, 6.6, -1.5, 0.35, false, 'hidden xl:block'], [12, 84, 2, 7.4, -3.3, 0.3, false, 'hidden xl:block'],
            [46, 3, 2, 5.4, -0.4, 0.3, false, 'hidden lg:block'], [2, 62, 3, 7, -4.8, 0.35, true, 'hidden xl:block'],
            // Small screens: the strip above the back link.
            [64, '6px', 2, 6, -1.4, 0.6, false, 'lg:hidden'], [82, '10px', 3, 6.6, -3.8, 0.7, true, 'lg:hidden'],
            [93, '30px', 2, 5.4, -0.4, 0.6, false, 'lg:hidden'], [50, '8px', 2, 7.2, -4.4, 0.45, false, 'lg:hidden'],
        ];

        $fireworks = [
            ['right: 16%; top: 5%', 22, 11, 1.5, 'hidden lg:block'],
            ['right: 27%; top: 88%', 20, 13, 6, 'hidden lg:block'],
            ['left: 3%; top: 22%', 20, 12, 3.5, 'hidden xl:block'],
            ['left: 4%; bottom: 18%', 18, 14, 9, 'hidden xl:block'],
            ['right: 9%; top: 48px', 16, 12, 2, 'lg:hidden'],
        ];
    }

    if ($layout === 'centered') {
        $caps = [
            ['left: 6%; top: 16%', 56, 8, 7.5, 0, 0.8, 'hidden lg:block'],
            ['right: 6%; top: 20%', 62, -8, 7, -1, 0.85, 'hidden lg:block'],
            ['right: 14%; top: 56%', 36, 10, 9, -3, 0.5, 'hidden lg:block'],
            ['right: 6%; top: 4%', 30, -10, 7, 0, 0.7, 'lg:hidden'],
            ['left: 7%; top: 8%', 22, 12, 8, -2.5, 0.5, 'lg:hidden'],
        ];

        $sparks = [
            [3, 26, 3, 5.5, 0, 0.7, false, 'hidden lg:block'], [9, 58, 4, 6.5, -1.2, 0.8, true, 'hidden lg:block'],
            [15, 30, 2, 4.8, -2.4, 0.6, false, 'hidden lg:block'], [6, 86, 3, 6, -0.6, 0.7, false, 'hidden lg:block'],
            [17, 72, 2, 5.6, -3.4, 0.55, false, 'hidden xl:block'], [12, 12, 4, 6.8, -0.3, 0.75, true, 'hidden lg:block'],
            [97, 30, 3, 6.4, -4, 0.7, false, 'hidden lg:block'], [90, 46, 5, 7, -3.1, 0.85, true, 'hidden lg:block'],
            [86, 14, 2, 5, -2.2, 0.6, false, 'hidden lg:block'], [93, 66, 3, 5.2, -1.8, 0.7, false, 'hidden lg:block'],
            [84, 88, 2, 6.2, -1, 0.55, false, 'hidden xl:block'], [96, 92, 3, 5.8, -2.9, 0.6, true, 'hidden lg:block'],
            [30, 4, 2, 6, -1.4, 0.35, false, 'hidden lg:block'], [68, 3, 2, 6.6, -3.8, 0.35, false, 'hidden lg:block'],
            // Small screens: the top corners beside the logo, and the far edges.
            [6, 3, 2, 6, -1.4, 0.6, false, 'lg:hidden'], [18, 15, 3, 6.6, -3.8, 0.7, true, 'lg:hidden'],
            [80, 13, 2, 5.4, -0.4, 0.6, false, 'lg:hidden'], [92, 15, 3, 7.2, -4.4, 0.65, true, 'lg:hidden'],
            [3, 40, 2, 6.8, -2, 0.45, false, 'lg:hidden'], [96, 52, 2, 6.2, -3, 0.45, false, 'lg:hidden'],
        ];

        $fireworks = [
            ['left: 5%; top: 10%', 26, 12, 1.5, 'hidden lg:block'],
            ['right: 9%; bottom: 12%', 24, 13, 5.5, 'hidden lg:block'],
            ['right: 4%; top: 40%', 20, 14, 9.5, 'hidden xl:block'],
            ['left: 9%; bottom: 20%', 18, 15, 12, 'hidden xl:block'],
            ['right: 14%; top: 9%', 16, 12, 2, 'lg:hidden'],
        ];
    }
@endphp

<div class="hero-fx" aria-hidden="true">
    @foreach ($sparks as [$left, $top, $size, $duration, $delay, $peak, $star, $visibility])
        <span class="hero-fx__spark{{ $star ? ' hero-fx__spark--star' : '' }} {{ $visibility }}"
              style="left: {{ $left }}%; top: {{ is_string($top) ? $top : $top.'%' }}; width: {{ $star ? $size * 2 : $size }}px; height: {{ $star ? $size * 2 : $size }}px; --duration: {{ $duration }}s; --delay: {{ $delay }}s; --peak: {{ $peak }}"></span>
    @endforeach

    @foreach ($fireworks as [$position, $radius, $cycle, $delay, $visibility])
        <span class="hero-fx__firework {{ $visibility }}" style="{{ $position }}">
            @for ($angle = 0; $angle < 360; $angle += 30)
                <span class="hero-fx__ray" style="--angle: {{ $angle }}deg; --radius: {{ $radius }}px; --cycle: {{ $cycle }}s; --delay: {{ $delay }}s"></span>
            @endfor
        </span>
    @endforeach

    @foreach ($caps as [$position, $size, $tilt, $duration, $delay, $opacity, $visibility])
        <svg class="hero-fx__cap {{ $visibility }}" viewBox="0 0 64 48" width="{{ $size }}" height="{{ round($size * 0.75) }}"
             style="{{ $position }}; opacity: {{ $opacity }}; --tilt: {{ $tilt }}deg; --duration: {{ $duration }}s; --delay: {{ $delay }}s">
            {{-- Mortarboard: board, cap band, and tassel — gold on the dark hero. --}}
            <path d="M32 4 62 15 32 26 2 15Z" fill="currentColor" />
            <path d="M14 20.5v10c0 4.5 8 8 18 8s18-3.5 18-8v-10L32 27Z" fill="currentColor" opacity=".78" />
            <path d="M32 15 51 21v11" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
            <circle cx="51" cy="34" r="2.6" fill="currentColor" />
        </svg>
    @endforeach
</div>
