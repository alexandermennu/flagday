<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GalleryPhoto extends Model
{
    protected $fillable = ['position', 'caption', 'is_published', 'width', 'height'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function file(): HasOne
    {
        return $this->hasOne(GalleryPhotoFile::class);
    }

    /**
     * Public URL of the photo ('thumb' for the grid, 'full' for the lightbox). Versioned by
     * updated_at, so a replaced photo gets a new URL and the long cache never serves the old one.
     */
    public function url(string $variant = 'thumb'): string
    {
        return route('gallery.photo', [$this, $variant, 'v' => $this->updated_at?->timestamp]);
    }

    /** Same image for the admin, which can also see unpublished photos. */
    public function adminUrl(string $variant = 'thumb'): string
    {
        return route('admin.gallery.photos.image', [$this, $variant, 'v' => $this->updated_at?->timestamp]);
    }
}
