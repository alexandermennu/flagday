<?php

namespace App\Services;

/**
 * Draws the fixed decorative elements of the program PDF with GD — the cover starfield,
 * the orange-to-red gradient ribbons, the glow behind "AWARDS", the divider ornament, and
 * the faint pattern behind the award categories. dompdf has no CSS gradient or filter
 * support, so these are rendered as images and layered behind the live text.
 *
 * Everything is deterministic (seeded), so a regenerated PDF looks identical, and
 * memoized per process since the same art is reused across fit-check renders.
 */
class ProgramArtwork
{
    /** @var array<string, string> */
    private static array $cache = [];

    /** Gold and white speckles on black, denser toward the lower half — the cover background. */
    public static function starfield(int $width = 1684, int $height = 1190): string
    {
        return self::$cache["starfield:$width:$height"] ??= (function () use ($width, $height) {
            $image = imagecreatetruecolor($width, $height);
            imagefill($image, 0, 0, imagecolorallocate($image, 0, 0, 0));

            mt_srand(2026);
            $palette = [[214, 178, 108], [240, 226, 196], [190, 150, 90], [255, 255, 255]];

            for ($i = 0; $i < 4200; $i++) {
                $x = mt_rand(0, $width);
                $y = mt_rand(0, $height);

                // Thin out the top band behind the university name so it stays crisp.
                $density = 0.25 + 0.75 * min(1, $y / ($height * 0.55));
                if (mt_rand() / mt_getrandmax() > $density) {
                    continue;
                }

                [$r, $g, $b] = $palette[mt_rand(0, count($palette) - 1)];
                $brightness = mt_rand(25, 85) / 100;
                $color = imagecolorallocate($image, (int) ($r * $brightness), (int) ($g * $brightness), (int) ($b * $brightness));
                $size = mt_rand(0, 100) < 85 ? mt_rand(1, 3) : mt_rand(3, 5);
                imagefilledellipse($image, $x, $y, $size, $size, $color);
            }

            mt_srand();

            return self::jpeg($image);
        })();
    }

    /**
     * A rounded ribbon with a horizontal orange → red → orange gradient, sized in points
     * (rendered at 3x for print sharpness).
     */
    public static function ribbon(float $widthPt, float $heightPt, float $radiusPt = 6): string
    {
        $key = sprintf('ribbon:%.1f:%.1f:%.1f', $widthPt, $heightPt, $radiusPt);

        return self::$cache[$key] ??= (function () use ($widthPt, $heightPt, $radiusPt) {
            $scale = 3;
            $w = max(2, (int) round($widthPt * $scale));
            $h = max(2, (int) round($heightPt * $scale));
            $r = (int) round($radiusPt * $scale);

            $image = self::transparentCanvas($w, $h);
            $stops = [[0, [247, 160, 40]], [0.5, [233, 42, 46]], [1, [247, 160, 40]]];

            for ($x = 0; $x < $w; $x++) {
                $color = imagecolorallocate($image, ...self::interpolate($stops, $x / max(1, $w - 1)));
                imageline($image, $x, 0, $x, $h - 1, $color);
            }

            self::roundCorners($image, $w, $h, $r);

            return self::png($image);
        })();
    }

    /** A soft elliptical glow (orange by default) that fades to transparent. */
    public static function glow(int $width = 900, int $height = 360, array $rgb = [244, 110, 30]): string
    {
        $key = "glow:$width:$height:".implode(',', $rgb);

        return self::$cache[$key] ??= (function () use ($width, $height, $rgb) {
            $image = self::transparentCanvas($width, $height);
            imagealphablending($image, false);

            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $dx = ($x - $width / 2) / ($width / 2);
                    $dy = ($y - $height / 2) / ($height / 2);
                    $distance = sqrt($dx * $dx + $dy * $dy);
                    $opacity = max(0, 1 - $distance) ** 2 * 0.55;
                    $alpha = 127 - (int) round($opacity * 127);
                    imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, $rgb[0], $rgb[1], $rgb[2], $alpha));
                }
            }

            return self::png($image);
        })();
    }

    /** The divider under the cover ribbon: two fading rose-gold rules around a looped flourish. */
    public static function ornament(): string
    {
        return self::$cache['ornament'] ??= (function () {
            $w = 1200;
            $h = 120;
            $image = self::transparentCanvas($w, $h);
            $mid = (int) ($h / 2);

            // Rules fade out toward the edges.
            for ($x = 0; $x < $w; $x++) {
                $fromCenter = abs($x - $w / 2) / ($w / 2);
                if ($fromCenter < 0.14) {
                    continue;
                }
                $alpha = (int) round(127 * min(1, max(0, ($fromCenter - 0.14) / 0.86)) ** 1.6);
                $color = imagecolorallocatealpha($image, 214, 160, 150, $alpha);
                imagefilledrectangle($image, $x, $mid - 1, $x, $mid + 1, $color);
            }

            imagesetthickness($image, 5);
            $rose = imagecolorallocate($image, 226, 170, 160);
            $red = imagecolorallocate($image, 200, 40, 45);

            // Two pairs of loops either side of center, joined by a red swash.
            foreach ([-110, -60, 60, 110] as $i => $offset) {
                imagearc($image, (int) ($w / 2 + $offset), $mid - 12, 40, 70, 0, 360, $rose);
            }
            imagearc($image, (int) ($w / 2), $mid + 8, 140, 40, 20, 160, $red);

            return self::png($image);
        })();
    }

    /** Large, very light grey shapes punched with dots — the award page backdrop. */
    public static function pattern(int $width = 1684, int $height = 1190): string
    {
        return self::$cache["pattern:$width:$height"] ??= (function () use ($width, $height) {
            $image = imagecreatetruecolor($width, $height);
            imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
            $grey = imagecolorallocate($image, 238, 238, 238);
            $white = imagecolorallocate($image, 255, 255, 255);

            mt_srand(1844);
            $shapes = [[140, 160, 0.5], [1500, 120, -0.4], [380, 1050, 0.9], [1250, 980, -0.8], [820, 80, 0.2], [70, 700, -0.2], [1640, 640, 0.6]];

            foreach ($shapes as [$cx, $cy, $angle]) {
                $points = [];
                $halfW = mt_rand(180, 260);
                $halfH = mt_rand(70, 110);
                foreach ([[-1, -1], [1, -1], [1, 1], [-1, 1]] as [$sx, $sy]) {
                    $px = $sx * $halfW;
                    $py = $sy * $halfH;
                    $points[] = (int) ($cx + $px * cos($angle) - $py * sin($angle));
                    $points[] = (int) ($cy + $px * sin($angle) + $py * cos($angle));
                }
                imagefilledpolygon($image, $points, $grey);

                for ($d = 0; $d < 22; $d++) {
                    $px = mt_rand(-$halfW + 25, $halfW - 25);
                    $py = mt_rand(-$halfH + 20, $halfH - 20);
                    $dotX = (int) ($cx + $px * cos($angle) - $py * sin($angle));
                    $dotY = (int) ($cy + $px * sin($angle) + $py * cos($angle));
                    imagefilledellipse($image, $dotX, $dotY, mt_rand(14, 22), mt_rand(26, 36), $white);
                }
            }

            mt_srand();

            return self::jpeg($image, 82);
        })();
    }

    private static function transparentCanvas(int $w, int $h)
    {
        $image = imagecreatetruecolor($w, $h);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        imagealphablending($image, true);

        return $image;
    }

    /** Clears the pixels outside each rounded corner, with a 1px anti-aliased edge. */
    private static function roundCorners($image, int $w, int $h, int $r): void
    {
        if ($r <= 0) {
            return;
        }

        imagealphablending($image, false);
        $r = min($r, (int) floor(min($w, $h) / 2));

        foreach ([[$r, $r], [$w - $r - 1, $r], [$r, $h - $r - 1], [$w - $r - 1, $h - $r - 1]] as [$cx, $cy]) {
            $xStart = $cx < $w / 2 ? 0 : $cx;
            $yStart = $cy < $h / 2 ? 0 : $cy;

            for ($x = $xStart; $x <= $xStart + $r; $x++) {
                for ($y = $yStart; $y <= $yStart + $r; $y++) {
                    $distance = sqrt(($x - $cx) ** 2 + ($y - $cy) ** 2);
                    if ($distance <= $r - 1) {
                        continue;
                    }
                    $rgb = imagecolorat($image, min($x, $w - 1), min($y, $h - 1)) & 0xFFFFFF;
                    $coverage = max(0, min(1, $r - $distance));
                    $alpha = 127 - (int) round($coverage * 127);
                    imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, ($rgb >> 16) & 255, ($rgb >> 8) & 255, $rgb & 255, $alpha));
                }
            }
        }
    }

    /** @param  array<int, array{0: float, 1: array{0: int, 1: int, 2: int}}>  $stops */
    private static function interpolate(array $stops, float $t): array
    {
        for ($i = 1; $i < count($stops); $i++) {
            [$t1, $c1] = $stops[$i];
            [$t0, $c0] = $stops[$i - 1];
            if ($t <= $t1) {
                $local = ($t - $t0) / max(0.0001, $t1 - $t0);

                return array_map(fn ($a, $b) => (int) round($a + ($b - $a) * $local), $c0, $c1);
            }
        }

        return end($stops)[1];
    }

    private static function png($image): string
    {
        ob_start();
        imagepng($image, null, 9);

        return 'data:image/png;base64,'.base64_encode(ob_get_clean());
    }

    private static function jpeg($image, int $quality = 85): string
    {
        ob_start();
        imagejpeg($image, null, $quality);

        return 'data:image/jpeg;base64,'.base64_encode(ob_get_clean());
    }
}
