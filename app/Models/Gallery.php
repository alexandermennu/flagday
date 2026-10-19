<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An admin-managed photo gallery, identified by key. The Honors Ball page shows the
 * "honors-ball" gallery; its title, subtitle and visibility are edited in the admin.
 */
class Gallery extends Model
{
    public const HONORS_BALL = 'honors-ball';

    /** How many published photos the page shows: two carousel slides of eight. */
    public const MAX_PUBLISHED = 16;

    public const PER_SLIDE = 8;

    protected $fillable = ['key', 'title', 'subtitle', 'is_visible'];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean'];
    }

    public function photos(): HasMany
    {
        return $this->hasMany(GalleryPhoto::class)->orderBy('position')->orderBy('id');
    }

    /** The Honors Ball gallery, created hidden on first use so the admin can set it up. */
    public static function honorsBall(): self
    {
        return static::firstOrCreate(
            ['key' => self::HONORS_BALL],
            ['title' => 'Honors Ball Gallery', 'is_visible' => false],
        );
    }

    /** Published photos in display order, capped at what the carousel shows. */
    public function publishedPhotos()
    {
        return $this->photos()->where('is_published', true)->limit(self::MAX_PUBLISHED)->get();
    }

    /** Largest photo upload accepted, in KB (configurable via GALLERY_PHOTO_MAX_KB). */
    public static function photoUploadMaxKb(): int
    {
        return (int) config('graduation.gallery_photo_max_kb');
    }
}
