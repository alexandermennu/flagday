<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

/**
 * Normalizes an uploaded program image for storage and PDF embedding: re-encodes it with
 * GD (so dompdf always receives a plain PNG or JPEG, whatever was uploaded), keeps
 * transparency, and scales anything larger than MAX_SIDE down to keep the PDF small.
 */
class ProgramImageProcessor
{
    private const MAX_SIDE = 1200;

    /**
     * @return array{mime: string, width: int, height: int, data: string}
     */
    public function process(UploadedFile $file): array
    {
        $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if ($image === false) {
            throw new InvalidArgumentException('The image could not be read.');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(1, self::MAX_SIDE / max($width, $height));

        if ($scale < 1) {
            $newWidth = max(1, (int) round($width * $scale));
            $newHeight = max(1, (int) round($height * $scale));
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $image = $resized;
            [$width, $height] = [$newWidth, $newHeight];
        }

        // JPEG uploads have no transparency to preserve; everything else becomes PNG.
        $isJpeg = in_array($file->getMimeType(), ['image/jpeg', 'image/jpg'], true);

        ob_start();
        if ($isJpeg) {
            imagejpeg($image, null, 88);
        } else {
            imagesavealpha($image, true);
            imagepng($image, null, 9);
        }
        $bytes = ob_get_clean();

        return [
            'mime' => $isJpeg ? 'image/jpeg' : 'image/png',
            'width' => $width,
            'height' => $height,
            'data' => base64_encode($bytes),
        ];
    }
}
