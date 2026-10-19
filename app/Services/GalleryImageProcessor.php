<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

/**
 * Prepares an uploaded gallery photo: turns it the right way up (phone photos record their
 * rotation in EXIF rather than in the pixels), then produces a full-size JPEG for the
 * lightbox and a 4:3 centre-cropped thumbnail for the grid.
 */
class GalleryImageProcessor
{
    private const FULL_MAX_SIDE = 2000;

    private const THUMB_WIDTH = 800;

    private const THUMB_HEIGHT = 600;

    /**
     * @return array{width: int, height: int, full: string, thumb: string} base64 JPEGs
     */
    public function process(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $image = @imagecreatefromstring((string) file_get_contents($path));

        if ($image === false) {
            throw new InvalidArgumentException('The image could not be read.');
        }

        $image = $this->orient($image, $path);
        $width = imagesx($image);
        $height = imagesy($image);

        // Full size, scaled down to fit FULL_MAX_SIDE.
        $scale = min(1, self::FULL_MAX_SIDE / max($width, $height));
        $fullWidth = max(1, (int) round($width * $scale));
        $fullHeight = max(1, (int) round($height * $scale));
        $full = $this->canvas($fullWidth, $fullHeight);
        imagecopyresampled($full, $image, 0, 0, 0, 0, $fullWidth, $fullHeight, $width, $height);

        // Thumbnail: the largest centred 4:3 area, scaled to THUMB_WIDTH × THUMB_HEIGHT.
        $ratio = self::THUMB_WIDTH / self::THUMB_HEIGHT;
        $cropWidth = min($width, (int) round($height * $ratio));
        $cropHeight = min($height, (int) round($width / $ratio));
        $thumb = $this->canvas(self::THUMB_WIDTH, self::THUMB_HEIGHT);
        imagecopyresampled($thumb, $image, 0, 0, (int) (($width - $cropWidth) / 2), (int) (($height - $cropHeight) / 2),
            self::THUMB_WIDTH, self::THUMB_HEIGHT, $cropWidth, $cropHeight);

        return [
            'width' => $fullWidth,
            'height' => $fullHeight,
            'full' => $this->jpeg($full, 85),
            'thumb' => $this->jpeg($thumb, 82),
        ];
    }

    /** Applies the EXIF orientation of camera/phone JPEGs so the photo displays upright. */
    private function orient($image, string $path)
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = (int) (@exif_read_data($path)['Orientation'] ?? 1);

        return match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }

    /** A white canvas, so transparent PNG/WebP areas don't turn black as JPEG. */
    private function canvas(int $width, int $height)
    {
        $canvas = imagecreatetruecolor($width, $height);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));

        return $canvas;
    }

    private function jpeg($image, int $quality): string
    {
        imageinterlace($image, true); // progressive: shows a preview while loading on slow connections
        ob_start();
        imagejpeg($image, null, $quality);

        return base64_encode(ob_get_clean());
    }
}
