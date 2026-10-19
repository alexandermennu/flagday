<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;
use App\Models\GalleryPhotoFile;
use Illuminate\Http\Response;

/** Serves published gallery photos (from the database) to the public pages. */
class GalleryPhotoController extends Controller
{
    public function show(GalleryPhoto $photo, string $variant): Response
    {
        // Only published photos of a visible gallery are public.
        abort_unless($photo->is_published && $photo->gallery?->is_visible, 404);

        // URLs carry ?v=<updated_at>, so a replaced photo gets a new URL and the long cache —
        // including Laravel Cloud's edge cache — never serves a stale image.
        return self::imageResponse($photo, $variant, 'public, max-age=31536000, immutable');
    }

    public static function imageResponse(GalleryPhoto $photo, string $variant, string $cacheControl): Response
    {
        $data = GalleryPhotoFile::where('gallery_photo_id', $photo->id)->value($variant === 'full' ? 'full' : 'thumb');
        abort_if($data === null, 404);

        return response(base64_decode($data), 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => $cacheControl,
        ]);
    }
}
