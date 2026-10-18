{{--
    Four-page graduation program (A4 landscape), rendered by App\Services\ProgramPdfRenderer.
    Pages: cover · agenda · honors (black) · award categories. All text is live; the
    decorative art ($art) is drawn by App\Services\ProgramArtwork. Units are points
    (842 × 595 per page). Variable-length text sits in normal flow so any overflow spills
    onto an extra page, which the renderer detects and corrects by shrinking ($fit).

    dompdf only distinguishes normal/bold, so each font weight is its own family and every
    rule sets font-weight: normal explicitly. Containers of scaled text set a small font-size
    of their own: a line box is never shorter than its container's font (the CSS "strut"),
    so without it, shrinking the text would not shrink the spacing. Scaled text also avoids
    unitless line-heights, which dompdf resolves against its 12pt default rather than the
    element's own size; it uses each font's natural spacing (about 1.36em) instead.
--}}
@php
    $a = $fit['agenda'];
    $w = $fit['awards'];
    $up = fn (?string $s) => mb_strtoupper((string) $s);

    // dompdf ignores line-height for these faces and reserves the font's full box, so
    // display text sits in a box sized to its cap height with the glyphs shifted up by
    // the measured offset (fractions of the font size; calibrated against dompdf 3.1).
    $metrics = ['Anton' => [0.46, 0.87], 'MontXB' => [0.37, 0.70]];
    $capH = fn (float $fs, string $font = 'Anton') => round($fs * $metrics[$font][1], 1);
    $capTop = fn (float $fs, string $font = 'Anton') => round(-$fs * $metrics[$font][0], 1);
    // dompdf makes every line box line-height × the font's bounding-box height (measured
    // below), so line heights are set in points and divided by that factor to land exactly.
    // Leading tightens as text shrinks: $maxEm at full size down to 1.3em at the smallest tier.
    $lineFactor = ['OpenSans' => 1.498, 'Mont' => 1.341];
    $lh = fn (float $fs, float $em, string $font = 'OpenSans') => round($fs * $em / $lineFactor[$font], 2);
    $minScale = min(\App\Services\ProgramPdfRenderer::SCALES);
    $leading = fn (float $scale, float $maxEm) => 1.3 + ($maxEm - 1.3) * max(0, $scale - $minScale) / (1 - $minScale);
    // Vertical offset that centers a single line of caps inside a box of the given height.
    $centerTop = fn (float $fs, float $boxH, string $font = 'Anton') => round(($boxH - $fs * $metrics[$font][1]) / 2 - $fs * $metrics[$font][0], 1);
    $sideW = max(60, (842 - $L['institutionW'] - 10) / 2);
    $honorsLine3Top = 40 + ($p->honors_line1 ? $capH($L['honorsLine1Fs']) + 14 : 0) + ($L['ribbonText'] !== '' ? $L['ribbonH'] + 14 : 0) + ($p->honors_line2 ? $capH($L['honorsFs']) + 14 : 0);
    $glowTop = $honorsLine3Top + $capH($L['honorsFs']) / 2 - 120;
    $agenda = $p->agenda ?? [];
    $fullDate = $p->event_date ? $p->event_date->format('l, F j, Y') : $placeholder;
    $timeLabel = $p->event_time ? date('g:i A', strtotime($p->event_time)) : $placeholder;
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @font-face { font-family: 'Anton'; font-weight: normal; src: url('{{ $fontDir }}/Anton-Regular.ttf') format('truetype'); }
    @font-face { font-family: 'MontReg'; font-weight: normal; src: url('{{ $fontDir }}/Montserrat-Regular.ttf') format('truetype'); }
    @font-face { font-family: 'MontMed'; font-weight: normal; src: url('{{ $fontDir }}/Montserrat-Medium.ttf') format('truetype'); }
    @font-face { font-family: 'MontSemi'; font-weight: normal; src: url('{{ $fontDir }}/Montserrat-SemiBold.ttf') format('truetype'); }
    @font-face { font-family: 'MontBold'; font-weight: normal; src: url('{{ $fontDir }}/Montserrat-Bold.ttf') format('truetype'); }
    @font-face { font-family: 'MontXB'; font-weight: normal; src: url('{{ $fontDir }}/Montserrat-ExtraBold.ttf') format('truetype'); }
    @font-face { font-family: 'OpenSans'; font-weight: normal; src: url('{{ $fontDir }}/OpenSans-Regular.ttf') format('truetype'); }
    @font-face { font-family: 'OpenSansSemi'; font-weight: normal; src: url('{{ $fontDir }}/OpenSans-SemiBold.ttf') format('truetype'); }
    @font-face { font-family: 'OpenSansBold'; font-weight: normal; src: url('{{ $fontDir }}/OpenSans-Bold.ttf') format('truetype'); }
    @font-face { font-family: 'GreatVibes'; font-weight: normal; src: url('{{ $fontDir }}/GreatVibes-Regular.ttf') format('truetype'); }

    @page { margin: 0; }
    * { font-weight: normal; }
    body { margin: 0; font-family: 'OpenSans', sans-serif; color: #111; }
    table { border-collapse: collapse; }
    td { padding: 0; vertical-align: top; }
    /* `anywhere` (not `break-word`) also stops a long word from widening its table column. */
    .wrap { overflow-wrap: anywhere; }

    .page { position: relative; width: 842pt; height: 594pt; page-break-after: always; }
    .page.last { page-break-after: auto; }
    .full { position: absolute; top: 0; left: 0; width: 842pt; height: 595pt; }
    .black { background: #000; }

    /* ---------- Cover ---------- */
    .corner-tab { position: absolute; top: 0; left: 693pt; width: 96pt; height: 150pt; background: #3a1712; text-align: center; }
    .corner-tab img { margin-top: 18pt; width: 76pt; height: 112pt; }
    .cover-accent { position: absolute; left: 662pt; top: 196pt; width: 200pt; height: 200pt; }
    .cover-head { position: relative; padding-top: 44pt; }
    .capbox { position: relative; }
    .capbox .glyphs { position: absolute; left: 0; width: 100%; text-align: center; white-space: nowrap; line-height: 1; }
    .cover-head td { vertical-align: middle; }
    .seal { width: 64pt; height: 64pt; }
    .institution { font-family: 'MontXB'; color: #e9e6df; letter-spacing: -0.5pt; }
    .address { position: relative; text-align: center; font-family: 'MontMed'; font-size: 15pt; letter-spacing: 1pt; color: #f2efe8; margin-top: 18pt; }
    .anton { font-family: 'Anton'; }
    .badge { position: relative; margin: 14pt auto 0 auto; }
    .badge img { position: absolute; top: 0; left: 0; }
    .badge .glyphs { position: absolute; left: 0; width: 100%; text-align: center; font-family: 'Anton'; color: #fff; letter-spacing: 0.6pt; white-space: nowrap; line-height: 1; }
    .ornament { position: relative; text-align: center; margin-top: 14pt; }
    .ornament img { width: 290pt; height: 29pt; }
    .info-row { position: relative; margin: 10pt auto 0 auto; }
    .info-row td { vertical-align: middle; }
    .icon-circle { width: 26pt; height: 26pt; border-radius: 13pt; background: #a3262a; text-align: center; }
    .icon-circle img { width: 14pt; height: 14pt; margin-top: 6pt; }
    .date-day { font-family: 'OpenSans'; font-size: 18pt; color: #e9e6df; padding-left: 8pt; }
    .date-rest { font-family: 'MontBold'; font-size: 18pt; color: #fff; }
    .time { font-family: 'MontXB'; font-size: 22pt; color: #fff; padding-left: 8pt; }
    .venue-name { font-family: 'MontXB'; color: #fff; line-height: 1.1; }
    .venue-address { font-family: 'OpenSans'; font-size: 12.5pt; color: #d8d5ce; }
    .cover-logo { position: absolute; left: 62pt; top: 476pt; width: 92pt; height: 92pt; }
    .mark { position: absolute; width: 170pt; height: 96pt; }
    .mark .small { position: absolute; left: 50pt; font-family: 'MontReg'; letter-spacing: 1pt; color: #fff; white-space: nowrap; }
    .mark .script { position: absolute; left: 0; top: 4pt; font-family: 'GreatVibes'; line-height: 1; white-space: nowrap; }

    /* ---------- Agenda ---------- */
    .panel-bg { position: absolute; top: 0; left: 540pt; width: 302pt; height: 595pt; background: #f6f1e6; }
    .rule { position: absolute; left: 62pt; width: 1.6pt; background: #111; }
    .agenda-logo { position: absolute; left: 30pt; top: 262pt; width: 66pt; height: 66pt; }
    .agenda-layout { position: relative; width: 842pt; }
    .agenda-main { width: 402pt; padding: 50pt 30pt 24pt 108pt; }
    .agenda-panel { width: 234pt; padding: 50pt 34pt 24pt 34pt; }
    .agenda-item { font-family: 'OpenSans'; color: #111; }
    .agenda-feature { font-family: 'OpenSansBold'; color: #000; }
    .agenda-section { font-family: 'OpenSansBold'; color: #000; }
    .hosted { font-family: 'MontXB'; color: #000; letter-spacing: 0.4pt; }
    .panel-kicker { font-family: 'MontBold'; font-size: 8.5pt; letter-spacing: 2pt; color: #b2690f; }
    .panel-title { font-family: 'MontXB'; color: #000; margin-top: 6pt; }
    .panel-edition { font-family: 'OpenSansSemi'; color: #6d5c45; margin-top: 4pt; }
    .panel-rule { height: 2pt; width: 44pt; background: #e8962a; margin: 14pt 0; }
    .panel-text { font-family: 'OpenSans'; color: #2b2b2b; text-align: justify; }
    .panel-label { font-family: 'MontBold'; font-size: 7.5pt; letter-spacing: 1.5pt; color: #b2690f; margin-top: 12pt; }
    .panel-value { font-family: 'MontSemi'; color: #111; margin-top: 2pt; }

    /* ---------- Honors ---------- */
    .glow { position: absolute; left: 121pt; width: 600pt; height: 240pt; }
    .honors-stack { position: relative; padding-top: 40pt; }
    .honors-line { font-family: 'Anton'; letter-spacing: 1pt; margin-bottom: 14pt; }
    .ribbon { position: relative; margin: 0 auto 14pt auto; }
    .ribbon img { position: absolute; top: 0; left: 0; }
    .ribbon .glyphs { position: absolute; left: 0; width: 100%; text-align: center; font-family: 'Anton'; color: #111; letter-spacing: 0.5pt; white-space: nowrap; line-height: 1; }
    .honors-foot { position: relative; margin: 12pt auto 0 auto; }
    .honors-foot td { vertical-align: middle; padding: 0 22pt; }

    /* ---------- Award categories ---------- */
    .awards-wrap { position: relative; padding: 30pt 48pt 0 48pt; }
    .awards-title { font-family: 'MontSemi'; font-size: 27pt; text-align: center; color: #000; letter-spacing: 0.5pt; margin-bottom: 18pt; }
    .awards-cols { width: 746pt; }
    .awards-col { width: 232pt; padding-right: 25pt; }
    .awards-col.last { padding-right: 0; }
    .award-square { width: 11pt; height: 11pt; background: #e31e24; margin-top: 1.5pt; }
    .award-name { font-family: 'MontBold'; color: #000; padding-left: 7pt; }
    .award-desc { font-family: 'OpenSans'; color: #111; text-align: justify; margin-top: 4pt; }
    .awards-footer { position: absolute; left: 48pt; top: 566pt; font-family: 'MontMed'; font-size: 10pt; color: #111; letter-spacing: 0.5pt; }
</style>
</head>
<body>
@foreach ($sections as $section)
    @php $last = $loop->last ? ' last' : ''; @endphp

    @if ($section === 'cover')
        {{-- ================= Page 1: Cover ================= --}}
        <div class="page{{ $last }}">
            <img class="full" src="{{ $art['starfield'] }}" alt="">

            @if ($img['corner_badge'])
                <div class="corner-tab"><img src="{{ $img['corner_badge'] }}" alt=""></div>
            @endif
            @if ($img['cover_accent'])
                <img class="cover-accent" src="{{ $img['cover_accent'] }}" alt="">
            @endif

            <div class="cover-head">
                <table style="width: 842pt">
                    <tr>
                        <td style="width: {{ $sideW }}pt; text-align: right; padding-right: 14pt">
                            @if ($img['seal_left'])<img class="seal" src="{{ $img['seal_left'] }}" alt="">@endif
                        </td>
                        <td>
                            <div class="capbox" style="height: {{ $capH($L['institutionFs'], 'MontXB') }}pt">
                                <div class="glyphs institution" style="top: {{ $capTop($L['institutionFs'], 'MontXB') }}pt; font-size: {{ $L['institutionFs'] }}pt">{{ $L['institution'] }}</div>
                            </div>
                        </td>
                        <td style="width: {{ $sideW }}pt; text-align: left; padding-left: 14pt">
                            @if ($img['seal_right'])<img class="seal" src="{{ $img['seal_right'] }}" alt="">@endif
                        </td>
                    </tr>
                </table>
            </div>

            <div class="address wrap">
                @if ($p->institution_address_line1){{ $up($p->institution_address_line1) }}<br>@endif
                {{ $up($p->institution_address_line2) }}
            </div>

            <div class="capbox anton" style="margin-top: 32pt; height: {{ $capH($L['classFs']) }}pt">
                <div class="glyphs" style="top: {{ $capTop($L['classFs']) }}pt; font-size: {{ $L['classFs'] }}pt; color: #ec9a2b; letter-spacing: 3pt">CLASS OF</div>
            </div>
            {{-- The year is drawn twice, offset, for the extruded 3D look of the reference cover. --}}
            <div class="capbox anton" style="margin-top: 16pt; height: {{ $capH($L['yearFs']) + 6 }}pt">
                <div class="glyphs" style="top: {{ $capTop($L['yearFs']) + 6 }}pt; margin-left: 6pt; font-size: {{ $L['yearFs'] }}pt; color: #7a3f0a; letter-spacing: 4pt">{{ $p->class_year }}</div>
                <div class="glyphs" style="top: {{ $capTop($L['yearFs']) }}pt; font-size: {{ $L['yearFs'] }}pt; color: #ec9a2b; letter-spacing: 4pt">{{ $p->class_year }}</div>
            </div>

            @if ($L['badgeText'] !== '')
                <div class="badge" style="margin-top: 20pt; width: {{ $L['badgeW'] }}pt; height: {{ $L['badgeH'] }}pt">
                    <img src="{{ $art['badge'] }}" style="width: {{ $L['badgeW'] }}pt; height: {{ $L['badgeH'] }}pt" alt="">
                    <div class="glyphs" style="top: {{ $centerTop($L['badgeFs'], $L['badgeH']) }}pt; font-size: {{ $L['badgeFs'] }}pt">{{ $L['badgeText'] }}</div>
                </div>
            @endif

            <div class="ornament"><img src="{{ $art['ornament'] }}" alt=""></div>

            <table class="info-row">
                <tr>
                    <td><div class="icon-circle"><img src="{{ $icon('calendar') }}" alt=""></div></td>
                    <td class="date-day">{{ $L['dateDay'] }}&nbsp;<span class="date-rest">{{ $L['dateRest'] }}</span></td>
                    <td style="width: 28pt"></td>
                    <td><div class="icon-circle"><img src="{{ $icon('clock') }}" alt=""></div></td>
                    <td class="time">{{ $L['time'] }}</td>
                </tr>
            </table>

            <table class="info-row" style="margin-top: 8pt">
                <tr>
                    <td style="padding-right: 9pt"><div class="icon-circle"><img src="{{ $icon('building') }}" alt=""></div></td>
                    <td class="wrap" style="max-width: 360pt">
                        <div class="venue-name" style="font-size: {{ $L['venueFs'] }}pt">{{ $p->venue_name ? $up($p->venue_name) : 'VENUE TO BE ANNOUNCED' }}</div>
                        @if ($p->venue_address)<div class="venue-address">{{ $up($p->venue_address) }}</div>@endif
                    </td>
                </tr>
            </table>

            <img class="cover-logo" src="{{ $img['primary_logo'] }}" alt="">

            @include('pdf.partials.program-mark', ['p' => $p, 'style' => 'left: 612pt; top: 474pt', 'scriptColor' => '#f0a030'])
        </div>

    @elseif ($section === 'agenda')
        {{-- ================= Page 2: Agenda ================= --}}
        <div class="page{{ $last }}">
            <div class="panel-bg"></div>
            <div class="rule" style="top: 60pt; height: 190pt"></div>
            <img class="agenda-logo" src="{{ $img['primary_logo'] }}" alt="">
            <div class="rule" style="top: 340pt; height: 190pt"></div>

            <table class="agenda-layout">
                <tr>
                    <td class="agenda-main wrap" style="font-size: 4pt; line-height: 3pt">
                        @foreach ($agenda as $item)
                            @php $style = $item['style'] ?? 'item'; @endphp
                            @if ($style === 'feature')
                                <div class="agenda-feature" style="font-size: {{ 23 * $a }}pt; line-height: {{ $lh(23 * $a, 1.2) }}pt; margin: {{ 11 * $a }}pt 0 {{ 10 * $a }}pt 0">{{ $item['title'] }}</div>
                            @elseif ($style === 'section')
                                <div class="agenda-section" style="font-size: {{ 14.5 * $a }}pt; line-height: {{ $lh(14.5 * $a, $leading($a, 1.8)) }}pt; margin: {{ 12 * $a }}pt 0 {{ 3 * $a }}pt 0">{{ $item['title'] }}</div>
                            @else
                                <div class="agenda-item" style="font-size: {{ 14.5 * $a }}pt; line-height: {{ $lh(14.5 * $a, $leading($a, 1.8)) }}pt; margin-bottom: {{ 5 * $a }}pt">{{ $item['title'] }}</div>
                            @endif
                        @endforeach

                        @if ($p->hosted_by)
                            <div class="hosted" style="font-size: {{ 12.5 * $a }}pt; line-height: {{ $lh(12.5 * $a, 1.3, 'Mont') }}pt; margin-top: {{ 22 * $a }}pt">HOSTED BY : {{ $up($p->hosted_by) }}</div>
                        @endif
                    </td>
                    <td class="agenda-panel wrap" style="font-size: 4pt; line-height: 3pt">
                        <div class="panel-kicker" style="line-height: {{ $lh(8.5, 1.5, 'Mont') }}pt">{{ $up($p->institution_name) }} &middot; CLASS OF {{ $p->class_year }}</div>
                        <div class="panel-title" style="font-size: {{ 20 * $a }}pt; line-height: {{ $lh(20 * $a, 1.15, 'Mont') }}pt">{{ $p->title }}</div>
                        @if ($p->edition_label)
                            <div class="panel-edition" style="font-size: {{ 10.5 * $a }}pt; line-height: {{ $lh(10.5 * $a, 1.35) }}pt">{{ $p->edition_label }}</div>
                        @endif
                        <div class="panel-rule"></div>

                        @if ($p->description)
                            <div class="panel-text" style="font-size: {{ 10.5 * $a }}pt; line-height: {{ $lh(10.5 * $a, $leading($a, 1.6)) }}pt">{!! nl2br(e($p->description)) !!}</div>
                        @endif

                        <div class="panel-label">DATE</div>
                        <div class="panel-value" style="font-size: {{ 11 * $a }}pt; line-height: {{ $lh(11 * $a, 1.4, 'Mont') }}pt">{{ $fullDate }}</div>
                        <div class="panel-label">TIME</div>
                        <div class="panel-value" style="font-size: {{ 11 * $a }}pt; line-height: {{ $lh(11 * $a, 1.4, 'Mont') }}pt">{{ $timeLabel }}</div>
                        <div class="panel-label">VENUE</div>
                        <div class="panel-value" style="font-size: {{ 11 * $a }}pt; line-height: {{ $lh(11 * $a, 1.4, 'Mont') }}pt">
                            {{ $p->venue_name ?: $placeholder }}
                            @if ($p->venue_address)<br><span style="font-family: 'OpenSans'">{{ $p->venue_address }}</span>@endif
                        </div>

                        @if ($p->contact_phone || $p->contact_email || $p->contact_website)
                            <div class="panel-label">CONTACT</div>
                            <div class="panel-value" style="font-size: {{ 10.5 * $a }}pt; line-height: {{ $lh(10.5 * $a, 1.45) }}pt; font-family: 'OpenSans'">
                                @foreach (array_filter([$p->contact_phone, $p->contact_email, $p->contact_website]) as $line)
                                    {{ $line }}@if (! $loop->last)<br>@endif
                                @endforeach
                            </div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

    @elseif ($section === 'honors')
        {{-- ================= Page 3: Honors ================= --}}
        <div class="page{{ $last }}">
            <div class="full black"></div>
            <img class="glow" src="{{ $art['glow'] }}" style="top: {{ $glowTop }}pt" alt="">

            <div class="honors-stack">
                @foreach ([
                    ['text' => $p->honors_line1, 'fs' => $L['honorsLine1Fs'], 'color' => '#ffffff'],
                    ['ribbon' => true],
                    ['text' => $p->honors_line2, 'fs' => $L['honorsFs'], 'color' => '#ffffff'],
                    ['text' => $p->honors_line3, 'fs' => $L['honorsFs'], 'color' => '#f47b20'],
                ] as $line)
                    @if (! empty($line['ribbon']))
                        @if ($L['ribbonText'] !== '')
                            <div class="ribbon" style="width: {{ $L['ribbonW'] }}pt; height: {{ $L['ribbonH'] }}pt">
                                <img src="{{ $art['ribbon'] }}" style="width: {{ $L['ribbonW'] }}pt; height: {{ $L['ribbonH'] }}pt" alt="">
                                <div class="glyphs" style="top: {{ $centerTop($L['ribbonFs'], $L['ribbonH']) }}pt; font-size: {{ $L['ribbonFs'] }}pt">{{ $L['ribbonText'] }}</div>
                            </div>
                        @endif
                    @elseif ($line['text'])
                        <div class="capbox honors-line" style="height: {{ $capH($line['fs']) }}pt">
                            <div class="glyphs" style="top: {{ $capTop($line['fs']) }}pt; font-size: {{ $line['fs'] }}pt; color: {{ $line['color'] }}">{{ $up($line['text']) }}</div>
                        </div>
                    @endif
                @endforeach
            </div>

            <table class="honors-foot">
                <tr>
                    @if ($img['seal_left'])<td><img class="seal" src="{{ $img['seal_left'] }}" alt=""></td>@endif
                    <td>@include('pdf.partials.program-mark', ['p' => $p, 'style' => 'position: relative', 'scriptColor' => '#d6252b'])</td>
                    @if ($img['seal_right'])<td><img class="seal" src="{{ $img['seal_right'] }}" alt=""></td>@endif
                </tr>
            </table>
        </div>

    @elseif ($section === 'awards')
        {{-- ================= Page 4: Award categories ================= --}}
        <div class="page{{ $last }}">
            <img class="full" src="{{ $art['pattern'] }}" alt="">

            <div class="awards-wrap">
                @if ($p->awards_heading)
                    <div class="awards-title wrap">{{ $up($p->awards_heading) }}</div>
                @endif

                <table class="awards-cols">
                    <tr>
                        @foreach ($L['awardColumns'] as $column)
                            <td class="awards-col{{ $loop->last ? ' last' : '' }}" style="font-size: 4pt; line-height: 3pt">
                                @foreach ($column as $award)
                                    <table style="width: 100%">
                                        <tr>
                                            <td style="width: 11pt; font-size: 4pt; line-height: 3pt"><div class="award-square"></div></td>
                                            <td class="award-name wrap" style="font-size: {{ 11.5 * $w }}pt; line-height: {{ $lh(11.5 * $w, 1.25, 'Mont') }}pt">{{ $up($award['name'] ?? '') }}</td>
                                        </tr>
                                    </table>
                                    @if (! empty($award['description']))
                                        <div class="award-desc wrap" style="font-size: {{ 10.3 * $w }}pt; line-height: {{ $lh(10.3 * $w, $leading($w, 1.75)) }}pt; margin-bottom: {{ 15 * $w }}pt">{{ $award['description'] }}</div>
                                    @else
                                        <div style="height: {{ 12 * $w }}pt"></div>
                                    @endif
                                @endforeach
                            </td>
                        @endforeach
                    </tr>
                </table>

                {{-- Keeps the columns clear of the footer line: if they reach this far, the page overflows and the renderer shrinks the text. --}}
                <div style="height: 40pt"></div>
            </div>

            @if ($p->contact_website)
                <div class="awards-footer">{{ $up($p->contact_website) }}</div>
            @endif
        </div>
    @endif
@endforeach
</body>
</html>
