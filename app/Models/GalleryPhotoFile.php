<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** The image bytes of a gallery photo (base64 JPEG): full size and a 4:3 thumbnail. */
class GalleryPhotoFile extends Model
{
    protected $fillable = ['gallery_photo_id', 'full', 'thumb'];
}
