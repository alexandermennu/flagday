<?php

namespace App\Services;

use App\Exceptions\ProgramDoesNotFit;
use App\Models\Program;
use App\Support\Graduation;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use Dompdf\FontMetrics;
use Dompdf\Options;
use Illuminate\Support\Facades\File;

/**
 * Renders a Program to its four-page A4 (landscape) PDF with dompdf.
 *
 * Fitting: the agenda and award-category pages hold variable amounts of text. Each is
 * first rendered on its own at decreasing text-size tiers until it fits on exactly one
 * page (overflowing text spills onto a second page, which is how overflow is detected).
 * The full document is then rendered with those tiers and must come out at exactly four
 * pages — otherwise ProgramDoesNotFit is thrown rather than producing a clipped or
 * overlapping program.
 */
class ProgramPdfRenderer
{
    public const PAGE_COUNT = 4;

    /** Text-size multipliers tried in order for the variable-length pages. */
    public const SCALES = [1.0, 0.93, 0.86, 0.8, 0.74, 0.68, 0.62];

    private const BLANK_PIXEL = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    /** Font files for the faces whose single-line text is sized to fit a width. */
    private const MEASURED_FONTS = [
        'Anton' => 'Anton-Regular.ttf',
        'MontXB' => 'Montserrat-ExtraBold.ttf',
        'MontReg' => 'Montserrat-Regular.ttf',
        'GreatVibes' => 'GreatVibes-Regular.ttf',
    ];

    private static ?FontMetrics $fontMetrics = null;

    public function render(Program $program): string
    {
        File::ensureDirectoryExists(storage_path('fonts'));
        $program->loadMissing('assets');

        $fit = $this->fit($program);

        [$bytes, $pages] = $this->renderHtml($this->html($program, $fit));

        if ($pages !== self::PAGE_COUNT) {
            foreach (['cover' => 'cover', 'honors' => 'honors page'] as $section => $label) {
                if ($this->renderHtml($this->html($program, $fit, $section, probe: true))[1] > 1) {
                    throw ProgramDoesNotFit::section($label);
                }
            }

            throw ProgramDoesNotFit::section('program');
        }

        return $bytes;
    }

    /**
     * Text-size tier for each variable-length page — the largest at which it fits.
     *
     * @return array{agenda: float, awards: float}
     */
    public function fit(Program $program): array
    {
        File::ensureDirectoryExists(storage_path('fonts'));
        $program->loadMissing('assets');

        return [
            'agenda' => $this->fitSection($program, 'agenda', 'agenda page'),
            'awards' => $this->fitSection($program, 'awards', 'award categories page'),
        ];
    }

    /** Largest text-size tier at which the section fits on a single page. */
    private function fitSection(Program $program, string $section, string $label): float
    {
        foreach (self::SCALES as $scale) {
            $html = $this->html($program, [$section => $scale], $section, probe: true);

            if ($this->renderHtml($html)[1] === 1) {
                return $scale;
            }
        }

        throw ProgramDoesNotFit::section($label);
    }

    /**
     * @return array{0: string, 1: int} PDF bytes and page count
     */
    private function renderHtml(string $html): array
    {
        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');
        $pdf->setOption('isFontSubsettingEnabled', true);
        $pdf->setOption('isRemoteEnabled', false);

        $bytes = $pdf->output();

        return [$bytes, $pdf->getDomPDF()->getCanvas()->get_page_count()];
    }

    /**
     * @param  array<string, float>  $fit  text-size tier per section
     * @param  string|null  $only  render just this section (for fit checks)
     * @param  bool  $probe  swap images for a blank pixel — sizes are fixed in CSS, so
     *                       layout is identical and fit checks render much faster
     */
    public function html(Program $program, array $fit = [], ?string $only = null, bool $probe = false): string
    {
        $image = fn (string $slot) => ($uri = $program->imageDataUri($slot)) ? ($probe ? self::BLANK_PIXEL : $uri) : null;
        $art = fn (callable $make) => $probe ? self::BLANK_PIXEL : $make();

        $institution = mb_strtoupper($program->institution_name);
        $hasSeals = $program->asset('seal_left') || $program->asset('seal_right');
        $badgeText = mb_strtoupper((string) $program->edition_label);
        $ribbonText = mb_strtoupper((string) $program->honors_ribbon);

        $badgeFs = $this->fitText($badgeText, 24, 540, 'Anton', 0.6);
        $ribbonFs = $this->fitText($ribbonText, 42, 520, 'Anton', 0.5);
        $honorsFs = min(
            $this->fitText(mb_strtoupper((string) $program->honors_line2), 126, 700, 'Anton', 1),
            $this->fitText(mb_strtoupper((string) $program->honors_line3), 126, 700, 'Anton', 1),
        );

        $layout = [
            'institution' => $institution,
            'institutionFs' => $institutionFs = $this->fitText($institution, 52, $hasSeals ? 540 : 700, 'MontXB', -0.5),
            'institutionW' => $this->textWidth($institution, 'MontXB', $institutionFs, -0.5),
            'classFs' => 80,
            'yearFs' => $this->fitText((string) $program->class_year, 98, 600, 'Anton', 4),
            'badgeText' => $badgeText,
            'badgeFs' => $badgeFs,
            'badgeW' => round($this->textWidth($badgeText, 'Anton', $badgeFs, 0.6) + 46, 1),
            'badgeH' => round($badgeFs * 1.55, 1),
            'venueFs' => min(18, max(11, $this->fitText(mb_strtoupper((string) $program->venue_name), 18, 360, 'MontXB'))),
            'dateDay' => $program->event_date?->format('l').($program->event_date ? ',' : ''),
            'dateRest' => $program->event_date ? $this->shortDate($program) : 'Date to be announced',
            'time' => $program->event_time ? date('g:i A', strtotime($program->event_time)) : 'Time to be announced',
            'honorsLine1Fs' => $this->fitText(mb_strtoupper((string) $program->honors_line1), 100, 640, 'Anton', 1),
            'ribbonText' => $ribbonText,
            'ribbonFs' => $ribbonFs,
            'ribbonW' => round($this->textWidth($ribbonText, 'Anton', $ribbonFs, 0.5) + 40, 1),
            'ribbonH' => round($ribbonFs * 1.3, 1),
            'honorsFs' => $honorsFs,
            'awardColumns' => $this->awardColumns($program->awards ?? []),
            // Wordmark: the script word spans the 170pt mark; the small caps lines sit right of its first letter.
            'markScriptFs' => $this->fitText((string) $program->mark_script, 54, 165, 'GreatVibes'),
            'markSmallFs' => min(
                $this->fitText(mb_strtoupper((string) $program->mark_top), 17, 108, 'MontReg', 1),
                $this->fitText(mb_strtoupper((string) $program->mark_bottom), 17, 108, 'MontReg', 1),
            ),
        ];

        $render = fn (string $section) => $only === null || $only === $section;

        return view('pdf.program', [
            'p' => $program,
            'L' => $layout,
            'fit' => $fit + ['agenda' => 1.0, 'awards' => 1.0],
            'sections' => array_values(array_filter(['cover', 'agenda', 'honors', 'awards'], $render)),
            'img' => [
                'primary_logo' => $image('primary_logo'),
                'seal_left' => $image('seal_left'),
                'seal_right' => $image('seal_right'),
                'corner_badge' => $image('corner_badge'),
                'cover_accent' => $image('cover_accent'),
            ],
            'art' => [
                'starfield' => $render('cover') ? $art(fn () => ProgramArtwork::starfield()) : null,
                'badge' => $render('cover') ? $art(fn () => ProgramArtwork::ribbon($layout['badgeW'], $layout['badgeH'], 5)) : null,
                'ornament' => $render('cover') ? $art(fn () => ProgramArtwork::ornament()) : null,
                'ribbon' => $render('honors') ? $art(fn () => ProgramArtwork::ribbon($layout['ribbonW'], $layout['ribbonH'], 7)) : null,
                'glow' => $render('honors') ? $art(fn () => ProgramArtwork::glow()) : null,
                'pattern' => $render('awards') ? $art(fn () => ProgramArtwork::pattern()) : null,
            ],
            'icon' => fn (string $name, string $color = '#ffffff') => $probe ? self::BLANK_PIXEL : IconFactory::dataUri($name, $color),
            'fontDir' => resource_path('fonts'),
            'placeholder' => Graduation::PLACEHOLDER,
        ])->render();
    }

    /**
     * Largest font size (pt), up to $maxPt, at which a single line of text fits the width —
     * measured from the font file's real glyph widths (via dompdf's own font metrics), with
     * a small safety margin. $letterSpacing (pt) matches the CSS letter-spacing used.
     */
    private function fitText(string $text, float $maxPt, float $widthPt, string $font, float $letterSpacing = 0): float
    {
        if ($text === '') {
            return $maxPt;
        }

        // Width grows linearly with size: width(size) = size × width(1pt) + characters × spacing.
        $perPoint = $this->textWidth($text, $font, 1);
        $spacing = mb_strlen($text) * $letterSpacing;
        $fitting = ($widthPt * 0.97 - $spacing) / max(0.001, $perPoint);

        return round(max(1, min($maxPt, $fitting)), 1);
    }

    private function textWidth(string $text, string $font, float $size, float $letterSpacing = 0): float
    {
        $metrics = self::fontMetrics();

        return $metrics->getTextWidth($text, $metrics->getFont($font), $size, 0, $letterSpacing);
    }

    /** dompdf font metrics with the program's display faces registered (once per process). */
    private static function fontMetrics(): FontMetrics
    {
        if (self::$fontMetrics === null) {
            File::ensureDirectoryExists(storage_path('fonts'));

            $options = new Options(app('dompdf.options'));
            $options->setChroot(base_path());
            $metrics = (new Dompdf($options))->getFontMetrics();

            foreach (self::MEASURED_FONTS as $family => $file) {
                $metrics->registerFont(['family' => $family, 'weight' => 'normal', 'style' => 'normal'], resource_path("fonts/{$file}"));
            }

            self::$fontMetrics = $metrics;
        }

        return self::$fontMetrics;
    }

    /** "Nov. 21, 2026" — abbreviated month with a period only when actually shortened. */
    private function shortDate(Program $program): string
    {
        $date = $program->event_date;
        $month = mb_strlen($date->format('F')) > 3 ? $date->format('M').'.' : $date->format('M');

        return $month.' '.$date->format('j, Y');
    }

    /**
     * Splits award categories, in order, into three columns of roughly equal height
     * (minimizing the tallest column), estimating height from text length.
     *
     * @param  array<int, array{name?: string, description?: string}>  $awards
     * @return array<int, array<int, array{name?: string, description?: string}>>
     */
    private function awardColumns(array $awards, int $columns = 3): array
    {
        $awards = array_values($awards);
        $n = count($awards);

        if ($n <= $columns) {
            return array_pad(array_map(fn ($award) => [$award], $awards), $columns, []);
        }

        $weights = array_map(
            fn ($award) => 140 + mb_strlen($award['name'] ?? '') * 2.2 + mb_strlen($award['description'] ?? ''),
            $awards,
        );

        // prefix[i] = total weight of the first i awards
        $prefix = [0];
        foreach ($weights as $weight) {
            $prefix[] = end($prefix) + $weight;
        }

        // best[k][i] = smallest possible tallest column when splitting the first i awards into k columns
        $best = array_fill(0, $columns + 1, array_fill(0, $n + 1, INF));
        $cut = $best;
        $best[0][0] = 0;

        for ($k = 1; $k <= $columns; $k++) {
            for ($i = 1; $i <= $n; $i++) {
                for ($j = $k - 1; $j < $i; $j++) {
                    $candidate = max($best[$k - 1][$j], $prefix[$i] - $prefix[$j]);
                    if ($candidate < $best[$k][$i]) {
                        $best[$k][$i] = $candidate;
                        $cut[$k][$i] = $j;
                    }
                }
            }
        }

        $result = [];
        $end = $n;
        for ($k = $columns; $k >= 1; $k--) {
            $start = $cut[$k][$end];
            array_unshift($result, array_slice($awards, $start, $end - $start));
            $end = $start;
        }

        return $result;
    }
}
