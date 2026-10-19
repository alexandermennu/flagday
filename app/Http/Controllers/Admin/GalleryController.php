<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GalleryPhotoController;
use App\Models\Gallery;
use App\Models\GalleryPhoto;
use App\Services\GalleryImageProcessor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Admin management of the Honors Ball photo gallery: section title, subtitle and
 * visibility, plus uploading, captioning, ordering, publishing, replacing and deleting
 * photos. At most Gallery::MAX_PUBLISHED photos can be published at once.
 */
class GalleryController extends Controller
{
    private const MAX_FILES_PER_UPLOAD = 20;

    public function __construct(private GalleryImageProcessor $images)
    {
    }

    public function edit(): View
    {
        $gallery = Gallery::honorsBall();

        return view('admin.gallery.edit', [
            'gallery' => $gallery,
            'photos' => $gallery->photos()->get(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:300'],
            'is_visible' => ['boolean'],
        ]);

        Gallery::honorsBall()->update($data + ['is_visible' => false]);

        return back()->with('success', 'Gallery settings saved.');
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'photos' => ['required', 'array', 'max:'.self::MAX_FILES_PER_UPLOAD],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.Gallery::photoUploadMaxKb()],
        ], [
            'photos.required' => 'Choose at least one photo to upload.',
            'photos.max' => 'Upload at most '.self::MAX_FILES_PER_UPLOAD.' photos at a time.',
        ], ['photos.*' => 'photo']);

        $gallery = Gallery::honorsBall();
        $published = $gallery->photos()->where('is_published', true)->count();
        $position = (int) $gallery->photos()->max('position');
        $added = 0;
        $addedUnpublished = 0;

        foreach ($request->file('photos') as $file) {
            $processed = $this->images->process($file);
            $publish = $published < Gallery::MAX_PUBLISHED;

            DB::transaction(function () use ($gallery, $processed, $publish, &$position) {
                $photo = $gallery->photos()->create([
                    'position' => ++$position,
                    'is_published' => $publish,
                    'width' => $processed['width'],
                    'height' => $processed['height'],
                ]);
                $photo->file()->create(['full' => $processed['full'], 'thumb' => $processed['thumb']]);
            });

            $publish ? $published++ : $addedUnpublished++;
            $added++;
        }

        $message = $added === 1 ? 'Photo uploaded.' : "{$added} photos uploaded.";
        if ($addedUnpublished > 0) {
            $message .= " {$addedUnpublished} saved as unpublished — the gallery shows up to ".Gallery::MAX_PUBLISHED.' published photos.';
        }

        return back()->with('success', $message);
    }

    /** Saves captions, publish states and order for all photos, and any replacement files. */
    public function updatePhotos(Request $request): RedirectResponse
    {
        $gallery = Gallery::honorsBall();
        $ids = $gallery->photos()->pluck('id')->all();

        $data = $request->validate([
            'order' => ['array'],
            'order.*' => ['integer', 'in:'.implode(',', $ids ?: [0])],
            'photos' => ['array'],
            'photos.*.caption' => ['nullable', 'string', 'max:200'],
            'photos.*.is_published' => ['boolean'],
            'photos.*.file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.Gallery::photoUploadMaxKb()],
        ], [], ['photos.*.caption' => 'caption', 'photos.*.file' => 'replacement photo']);

        $rows = collect($data['photos'] ?? [])->only($ids);

        $publishedCount = $rows->filter(fn ($row) => (bool) ($row['is_published'] ?? false))->count()
            + $gallery->photos()->where('is_published', true)->whereNotIn('id', $rows->keys())->count();
        if ($publishedCount > Gallery::MAX_PUBLISHED) {
            throw ValidationException::withMessages([
                'photos' => 'At most '.Gallery::MAX_PUBLISHED." photos can be published at once — {$publishedCount} are selected. Unpublish some and save again.",
            ]);
        }

        DB::transaction(function () use ($gallery, $rows, $data, $request) {
            $positions = array_flip(array_values($data['order'] ?? []));

            foreach ($gallery->photos()->get() as $photo) {
                $row = $rows->get($photo->id);
                if ($row !== null) {
                    $photo->caption = $row['caption'] ?? null;
                    $photo->is_published = (bool) ($row['is_published'] ?? false);
                }
                if (isset($positions[$photo->id])) {
                    $photo->position = $positions[$photo->id] + 1;
                }

                if ($file = $request->file("photos.{$photo->id}.file")) {
                    $processed = $this->images->process($file);
                    $photo->fill(['width' => $processed['width'], 'height' => $processed['height']]);
                    $photo->file()->updateOrCreate([], ['full' => $processed['full'], 'thumb' => $processed['thumb']]);
                    $photo->updated_at = now(); // new versioned URL, so caches pick up the replacement
                }

                $photo->save();
            }
        });

        return back()->with('success', 'Photos saved.');
    }

    public function destroyPhoto(GalleryPhoto $photo): RedirectResponse
    {
        abort_unless($photo->gallery->key === Gallery::HONORS_BALL, 404);
        $photo->delete();

        return back()->with('success', 'Photo deleted.');
    }

    /** Image preview for the admin list, including unpublished photos. */
    public function image(GalleryPhoto $photo, string $variant): Response
    {
        return GalleryPhotoController::imageResponse($photo, $variant, 'private, max-age=300');
    }
}
