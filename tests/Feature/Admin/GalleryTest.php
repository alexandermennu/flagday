<?php

namespace Tests\Feature\Admin;

use App\Models\Gallery;
use App\Models\GalleryPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): self
    {
        return $this->actingAs(User::factory()->create());
    }

    /** Uploads $count photos through the admin and returns them in display order. */
    private function upload(int $count): \Illuminate\Support\Collection
    {
        $files = array_map(fn ($i) => UploadedFile::fake()->image("photo-{$i}.jpg", 1600, 1200), range(1, $count));
        $this->admin()->post(route('admin.gallery.photos.store'), ['photos' => $files])->assertSessionHasNoErrors();

        return Gallery::honorsBall()->photos()->get();
    }

    private function showGallery(array $settings = []): void
    {
        Gallery::honorsBall()->update(array_merge(['title' => 'Moments from the Ball', 'is_visible' => true], $settings));
    }

    // ------------------------------------------------------------------ admin

    public function test_only_administrators_can_manage_the_gallery(): void
    {
        $this->get(route('admin.gallery.edit'))->assertRedirect(route('admin.login'));
        $this->put(route('admin.gallery.update'), ['title' => 'X'])->assertRedirect(route('admin.login'));
        $this->post(route('admin.gallery.photos.store'))->assertRedirect(route('admin.login'));

        $this->admin()->get(route('admin.gallery.edit'))->assertOk()->assertSee('Gallery section');
    }

    public function test_title_subtitle_and_visibility_are_editable(): void
    {
        $this->admin()->put(route('admin.gallery.update'), [
            'title' => 'Moments from the Ball',
            'subtitle' => 'Highlights from the evening.',
            'is_visible' => '1',
        ])->assertSessionHasNoErrors();

        $gallery = Gallery::honorsBall();
        $this->assertSame('Moments from the Ball', $gallery->title);
        $this->assertSame('Highlights from the evening.', $gallery->subtitle);
        $this->assertTrue($gallery->is_visible);

        $this->put(route('admin.gallery.update'), ['title' => 'Renamed', 'is_visible' => '0']);
        $this->assertFalse(Gallery::honorsBall()->is_visible);
        $this->assertSame('Renamed', Gallery::honorsBall()->title);

        $this->put(route('admin.gallery.update'), ['title' => ''])->assertSessionHasErrors('title');
    }

    public function test_multiple_photos_upload_with_full_and_4_by_3_thumbnail_versions(): void
    {
        $photos = $this->upload(3);

        $this->assertCount(3, $photos);
        $this->assertSame([1, 2, 3], $photos->pluck('position')->all());
        $this->assertTrue($photos->every->is_published);

        [$width, $height] = getimagesizefromstring(base64_decode($photos[0]->file->thumb));
        $this->assertSame([800, 600], [$width, $height]);
        $this->assertSame([1600, 1200], [$photos[0]->width, $photos[0]->height]);
    }

    public function test_sideways_phone_photos_are_turned_upright(): void
    {
        // A portrait photo stored landscape with EXIF Orientation 6 ("rotate 90° clockwise").
        $jpeg = $this->jpegWithOrientation(400, 300, 6);
        $file = UploadedFile::fake()->createWithContent('phone.jpg', $jpeg);

        $this->admin()->post(route('admin.gallery.photos.store'), ['photos' => [$file]])->assertSessionHasNoErrors();

        $photo = GalleryPhoto::sole();
        $this->assertSame([300, 400], [$photo->width, $photo->height]);
    }

    public function test_only_sixteen_photos_can_be_published(): void
    {
        $photos = $this->upload(18);

        $this->assertSame(16, $photos->where('is_published', true)->count(), 'Uploads beyond 16 are saved unpublished.');
        $this->assertFalse($photos[16]->is_published);

        // Trying to publish a 17th is refused.
        $rows = $photos->mapWithKeys(fn ($p) => [$p->id => ['caption' => '', 'is_published' => '1']])->all();
        $this->put(route('admin.gallery.photos.update'), ['photos' => $rows, 'order' => $photos->pluck('id')->all()])
            ->assertSessionHasErrors('photos');
        $this->assertSame(16, GalleryPhoto::where('is_published', true)->count());
    }

    public function test_captions_order_and_publishing_are_saved_together(): void
    {
        [$a, $b, $c] = $this->upload(3)->all();

        $this->put(route('admin.gallery.photos.update'), [
            'order' => [$c->id, $a->id, $b->id],
            'photos' => [
                $a->id => ['caption' => 'First dance', 'is_published' => '1'],
                $b->id => ['caption' => '', 'is_published' => '0'],
                $c->id => ['caption' => 'Awards', 'is_published' => '1'],
            ],
        ])->assertSessionHasNoErrors();

        $photos = Gallery::honorsBall()->photos()->get();
        $this->assertSame([$c->id, $a->id, $b->id], $photos->pluck('id')->all());
        $this->assertSame('First dance', $photos[1]->caption);
        $this->assertNull($photos[2]->caption);
        $this->assertFalse($photos[2]->is_published);
    }

    public function test_photos_can_be_replaced_and_deleted(): void
    {
        [$a, $b] = $this->upload(2)->all();
        $oldUrl = $a->url();
        $this->travel(5)->seconds();

        $this->put(route('admin.gallery.photos.update'), [
            'order' => [$a->id, $b->id],
            'photos' => [
                $a->id => ['caption' => 'Replaced', 'is_published' => '1', 'file' => UploadedFile::fake()->image('new.jpg', 1000, 1000)],
                $b->id => ['caption' => '', 'is_published' => '1'],
            ],
        ])->assertSessionHasNoErrors();

        $a->refresh();
        $this->assertSame([1000, 1000], [$a->width, $a->height]);
        $this->assertNotSame($oldUrl, $a->url(), 'A replaced photo gets a new URL so caches refresh.');

        $this->delete(route('admin.gallery.photos.destroy', $b))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('gallery_photos', ['id' => $b->id]);
        $this->assertDatabaseMissing('gallery_photo_files', ['gallery_photo_id' => $b->id]);
    }

    public function test_non_images_are_rejected(): void
    {
        $this->admin()->post(route('admin.gallery.photos.store'), [
            'photos' => [UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')],
        ])->assertSessionHasErrors('photos.0');

        $this->assertDatabaseCount('gallery_photos', 0);
    }

    // ------------------------------------------------------------------ public page

    public function test_honors_ball_page_shows_the_gallery_in_place_of_more_graduation_events(): void
    {
        $this->upload(3);
        $this->showGallery(['subtitle' => 'Highlights from the evening.']);
        auth()->logout();

        $html = $this->get(route('events.show', 'honors-ball'))->assertOk()->getContent();

        $this->assertStringContainsString('Moments from the Ball', $html);
        $this->assertStringContainsString('Highlights from the evening.', $html);
        $this->assertStringNotContainsString('More Graduation Events', $html);
        $this->assertSame(3, substr_count($html, 'data-gallery-open='));
        // In the same place: after Event Details, at the end of the page content.
        $this->assertGreaterThan(strpos($html, 'Event Details</h2>'), strpos($html, 'data-gallery '));
    }

    public function test_sixteen_photos_make_two_slides_of_eight(): void
    {
        $this->upload(16);
        $this->showGallery();
        auth()->logout();

        $html = $this->get(route('events.show', 'honors-ball'))->getContent();

        $this->assertSame(2, substr_count($html, 'data-gallery-slide '));
        $this->assertSame(16, substr_count($html, 'data-gallery-open='));
        $this->assertStringContainsString('data-gallery-prev', $html);
        $this->assertStringContainsString('data-gallery-dot="1"', $html);
        // Slide 1 holds photos 1–8 and slide 2 photos 9–16.
        [$first, $second] = array_slice(explode('data-gallery-slide ', $html), 1);
        $this->assertSame(range(0, 7), array_map('intval', $this->openIndexes($first)));
        $this->assertSame(range(8, 15), array_map('intval', $this->openIndexes($second)));
    }

    public function test_hidden_gallery_or_no_published_photos_leaves_the_section_off(): void
    {
        $photos = $this->upload(2);
        auth()->logout();

        // Hidden (the default).
        $this->get(route('events.show', 'honors-ball'))->assertDontSee('data-gallery ', escape: false)->assertDontSee('More Graduation Events');

        // Visible, but nothing published.
        $this->showGallery();
        GalleryPhoto::query()->update(['is_published' => false]);
        $this->get(route('events.show', 'honors-ball'))->assertDontSee('data-gallery ', escape: false);
    }

    public function test_gallery_appears_only_on_the_honors_ball_page(): void
    {
        $this->upload(2);
        $this->showGallery();
        auth()->logout();

        foreach (['baccalaureate', 'convocation'] as $slug) {
            $this->get(route('events.show', $slug))
                ->assertOk()
                ->assertSee('More Graduation Events')
                ->assertDontSee('Moments from the Ball')
                ->assertDontSee('data-gallery ', escape: false);
        }
        $this->get(route('landing'))->assertDontSee('data-gallery ', escape: false);
        $this->get(route('class.show'))->assertDontSee('data-gallery ', escape: false);
    }

    public function test_published_photos_are_served_publicly_and_unpublished_ones_are_not(): void
    {
        [$a, $b] = $this->upload(2)->all();
        $b->update(['is_published' => false]);
        $this->showGallery();
        auth()->logout();

        $response = $this->get($a->url('thumb'));
        $response->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        $this->assertStringContainsString('immutable', $response->headers->get('Cache-Control'));
        $this->get($a->url('full'))->assertOk();

        $this->get($b->url())->assertNotFound();

        Gallery::honorsBall()->update(['is_visible' => false]);
        $this->get($a->url())->assertNotFound();

        // Administrators can still preview every photo.
        $this->admin()->get($b->adminUrl())->assertOk();
    }

    /** @return array<int, string> */
    private function openIndexes(string $html): array
    {
        preg_match_all('/data-gallery-open="(\d+)"/', $html, $matches);

        return $matches[1];
    }

    /** A JPEG with an EXIF Orientation tag, built by hand (GD can't write EXIF). */
    private function jpegWithOrientation(int $width, int $height, int $orientation): string
    {
        $image = imagecreatetruecolor($width, $height);
        ob_start();
        imagejpeg($image);
        $jpeg = ob_get_clean();

        // APP1 "Exif" segment: little-endian TIFF header with one IFD entry (0x0112 Orientation, SHORT).
        $tiff = 'II'.pack('v', 42).pack('V', 8).pack('v', 1)
            .pack('v', 0x0112).pack('v', 3).pack('V', 1).pack('v', $orientation).pack('v', 0)
            .pack('V', 0);
        $app1 = "Exif\0\0".$tiff;

        return substr($jpeg, 0, 2)."\xFF\xE1".pack('n', strlen($app1) + 2).$app1.substr($jpeg, 2);
    }
}
