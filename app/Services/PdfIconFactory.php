<?php

namespace App\Services;

/**
 * Renders small icon glyphs as base64 PNG data URIs for use inside the PDF ticket.
 * dompdf in this environment does not render inline <svg> markup (verified: even a
 * trivial <circle>/<rect> renders blank), so icons are drawn with GD instead — the same
 * reliable raster approach already used for the QR code image.
 */
class PdfIconFactory
{
    private const SIZE = 64;

    public static function dataUri(string $name, string $hexColor): string
    {
        $image = imagecreatetruecolor(self::SIZE, self::SIZE);
        imagealphablending($image, true);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);

        [$r, $g, $b] = self::hexToRgb($hexColor);
        $color = imagecolorallocate($image, $r, $g, $b);

        match ($name) {
            'check' => self::drawCheck($image, $color),
            'person' => self::drawPerson($image, $color),
            'building' => self::drawBuilding($image, $color),
            'briefcase' => self::drawBriefcase($image, $color),
            'calendar' => self::drawCalendar($image, $color),
            'clock' => self::drawClock($image, $color),
            'pin' => self::drawPin($image, $color),
            'tie' => self::drawTie($image, $color),
            'star' => self::drawStar($image, $color),
            'phone' => self::drawPhone($image, $color),
            'mail' => self::drawMail($image, $color),
            'globe' => self::drawGlobe($image, $color),
            default => null,
        };

        ob_start();
        imagepng($image);
        $bytes = ob_get_clean();

        return 'data:image/png;base64,'.base64_encode($bytes);
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private static function thickLine($image, int $color, int $x1, int $y1, int $x2, int $y2, int $thickness): void
    {
        imagesetthickness($image, $thickness);
        imageline($image, $x1, $y1, $x2, $y2, $color);
    }

    private static function drawCheck($image, int $color): void
    {
        self::thickLine($image, $color, 14, 34, 26, 46, 7);
        self::thickLine($image, $color, 26, 46, 50, 16, 7);
    }

    private static function drawPerson($image, int $color): void
    {
        imagefilledellipse($image, 32, 21, 22, 22, $color);
        imagefilledpolygon($image, [10, 54, 54, 54, 46, 34, 18, 34], $color);
    }

    private static function drawBuilding($image, int $color): void
    {
        imagefilledpolygon($image, [32, 8, 6, 26, 58, 26], $color);
        imagefilledrectangle($image, 12, 26, 20, 52, $color);
        imagefilledrectangle($image, 28, 26, 36, 52, $color);
        imagefilledrectangle($image, 44, 26, 52, 52, $color);
        imagefilledrectangle($image, 6, 52, 58, 58, $color);
    }

    private static function drawBriefcase($image, int $color): void
    {
        imagefilledrectangle($image, 8, 24, 56, 52, $color);
        self::thickLine($image, $color, 24, 24, 24, 16, 5);
        self::thickLine($image, $color, 40, 24, 40, 16, 5);
        self::thickLine($image, $color, 24, 16, 40, 16, 5);
    }

    private static function drawCalendar($image, int $color): void
    {
        imagesetthickness($image, 4);
        imagerectangle($image, 8, 12, 56, 56, $color);
        imageline($image, 8, 24, 56, 24, $color);
        self::thickLine($image, $color, 20, 6, 20, 16, 5);
        self::thickLine($image, $color, 44, 6, 44, 16, 5);
    }

    private static function drawClock($image, int $color): void
    {
        imagesetthickness($image, 4);
        imageellipse($image, 32, 32, 48, 48, $color);
        self::thickLine($image, $color, 32, 32, 32, 18, 4);
        self::thickLine($image, $color, 32, 32, 42, 38, 4);
    }

    private static function drawPin($image, int $color): void
    {
        imagefilledellipse($image, 32, 26, 34, 34, $color);
        imagefilledpolygon($image, [16, 32, 48, 32, 32, 58], $color);
    }

    private static function drawTie($image, int $color): void
    {
        imagefilledpolygon($image, [26, 4, 38, 4, 42, 16, 32, 24, 22, 16], $color);
        imagefilledpolygon($image, [26, 24, 38, 24, 46, 52, 32, 60, 18, 52], $color);
    }

    private static function drawStar($image, int $color): void
    {
        $points = [];
        $cx = 32;
        $cy = 32;
        $outerR = 28;
        $innerR = 11;

        for ($i = 0; $i < 10; $i++) {
            $angle = -M_PI / 2 + $i * M_PI / 5;
            $r = $i % 2 === 0 ? $outerR : $innerR;
            $points[] = $cx + $r * cos($angle);
            $points[] = $cy + $r * sin($angle);
        }

        imagefilledpolygon($image, $points, $color);
    }

    private static function drawPhone($image, int $color): void
    {
        imagefilledellipse($image, 32, 32, 40, 40, $color);
    }

    private static function drawMail($image, int $color): void
    {
        imagesetthickness($image, 4);
        imagerectangle($image, 6, 14, 58, 50, $color);
        imageline($image, 6, 14, 32, 36, $color);
        imageline($image, 58, 14, 32, 36, $color);
    }

    private static function drawGlobe($image, int $color): void
    {
        imagesetthickness($image, 3);
        imageellipse($image, 32, 32, 48, 48, $color);
        imageline($image, 8, 32, 56, 32, $color);
        imageellipse($image, 32, 32, 20, 48, $color);
    }
}
