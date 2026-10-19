@extends('admin.layout')

@section('title', 'Gallery')

@php
    $in = 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-gold-500 focus:outline-none focus:ring-1 focus:ring-gold-500';
    $label = 'mb-1 block text-sm font-semibold text-slate-700';
    $help = 'mt-1 text-xs text-slate-500';
    $max = \App\Models\Gallery::MAX_PUBLISHED;
    $publishedCount = $photos->where('is_published', true)->count();
    $photoMaxKb = \App\Support\UploadLimits::effectiveKb(\App\Models\Gallery::photoUploadMaxKb());
    $postMaxKb = \App\Support\UploadLimits::postMaxKb();
@endphp

@section('content')
    <p class="mb-6 max-w-3xl text-sm text-slate-500">
        The photo gallery on the <a href="{{ route('events.show', 'honors-ball') }}" target="_blank" rel="noopener" class="font-semibold text-ink underline">Honors Ball page</a>.
        It shows up to {{ $max }} published photos, eight per slide, in the order below.
    </p>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">Please correct the following:</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-6">
        {{-- ============ Section settings ============ --}}
        <form method="POST" action="{{ route('admin.gallery.update') }}" class="rounded-2xl border border-slate-200 bg-white p-6">
            @csrf
            @method('PUT')
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-ink">Gallery section</h2>
                    <p class="{{ $help }}">Shown on the Honors Ball page in place of the other events list.</p>
                </div>
                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $gallery->is_visible ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' }}">
                    {{ $gallery->is_visible ? 'Visible' : 'Hidden' }} &middot; {{ $publishedCount }} of {{ $max }} published
                </span>
            </div>

            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div>
                    <label for="title" class="{{ $label }}">Section title <span class="text-red-600">*</span></label>
                    <input type="text" name="title" id="title" required maxlength="120" value="{{ old('title', $gallery->title) }}" class="{{ $in }}">
                </div>
                <div>
                    <label for="subtitle" class="{{ $label }}">Subtitle or description</label>
                    <textarea name="subtitle" id="subtitle" rows="2" maxlength="300" class="{{ $in }}">{{ old('subtitle', $gallery->subtitle) }}</textarea>
                    <p class="{{ $help }}">Optional, up to 300 characters.</p>
                </div>
            </div>

            <label class="mt-5 flex items-center gap-2 text-sm font-medium text-slate-700">
                <input type="hidden" name="is_visible" value="0">
                <input type="checkbox" name="is_visible" value="1" @checked(old('is_visible', $gallery->is_visible)) class="h-4 w-4 rounded accent-gold-500">
                Show the gallery on the Honors Ball page
            </label>
            <p class="{{ $help }}">When hidden (or when no photos are published), the section is left off the page.</p>

            <button type="submit" class="mt-5 inline-flex items-center rounded-lg bg-ink px-5 py-2.5 text-sm font-semibold text-gold-300 transition hover:bg-neutral-800">
                Save settings
            </button>
        </form>

        {{-- ============ Upload ============ --}}
        <form method="POST" action="{{ route('admin.gallery.photos.store') }}" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white p-6">
            @csrf
            <h2 class="text-base font-bold text-ink">Upload photos</h2>
            <p class="{{ $help }}">
                JPEG, PNG or WebP, up to {{ number_format($photoMaxKb / 1024, 1) }} MB each and 20 at a time
                (the server accepts {{ number_format($postMaxKb / 1024, 1) }} MB per upload in total).
                Photos are resized automatically and shown cropped to 4:3. New photos are added at the end
                and published while fewer than {{ $max }} are published.
            </p>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <input type="file" name="photos[]" multiple required accept="image/jpeg,image/png,image/webp"
                       class="block text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                <button type="submit" class="inline-flex items-center rounded-lg bg-gold-400 px-5 py-2.5 text-sm font-semibold text-ink transition hover:bg-gold-300">
                    Upload
                </button>
            </div>
        </form>

        {{-- ============ Photos ============ --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-ink">Photos</h2>
                    <p class="{{ $help }}">Use the arrows to reorder. Published photos appear on the page in this order; the first eight form slide 1.</p>
                </div>
                <span data-published-counter data-max="{{ $max }}" class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                    {{ $publishedCount }} of {{ $max }} published
                </span>
            </div>

            @if ($photos->isEmpty())
                <p class="mt-6 rounded-xl border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500">No photos yet — upload some above.</p>
            @else
                <form method="POST" action="{{ route('admin.gallery.photos.update') }}" enctype="multipart/form-data" id="photos-form">
                    @csrf
                    @method('PUT')

                    <ol class="mt-5 space-y-3" data-sortable>
                        @foreach ($photos as $photo)
                            <li data-sortable-row class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-slate-50/60 p-3 sm:flex-row sm:items-center">
                                <input type="hidden" name="order[]" value="{{ $photo->id }}">
                                <div class="flex items-center gap-3">
                                    <span data-sortable-number class="w-6 text-right text-xs font-semibold text-slate-400">{{ $loop->iteration }}</span>
                                    <a href="{{ $photo->adminUrl('full') }}" target="_blank" rel="noopener" class="block shrink-0">
                                        <img src="{{ $photo->adminUrl() }}" alt="" width="120" height="90" loading="lazy"
                                             class="h-[90px] w-[120px] rounded-md object-cover {{ $photo->is_published ? '' : 'opacity-50 grayscale' }}">
                                    </a>
                                </div>

                                <div class="min-w-0 flex-1 space-y-2">
                                    <input type="text" name="photos[{{ $photo->id }}][caption]" value="{{ old("photos.{$photo->id}.caption", $photo->caption) }}" maxlength="200"
                                           placeholder="Caption (optional)" aria-label="Caption for photo {{ $loop->iteration }}" class="{{ $in }}">
                                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm">
                                        <label class="flex items-center gap-2 font-medium text-slate-700">
                                            <input type="hidden" name="photos[{{ $photo->id }}][is_published]" value="0">
                                            <input type="checkbox" name="photos[{{ $photo->id }}][is_published]" value="1" data-publish-toggle
                                                   @checked(old("photos.{$photo->id}.is_published", $photo->is_published)) class="h-4 w-4 rounded accent-gold-500">
                                            Published
                                        </label>
                                        <label class="flex items-center gap-2 text-slate-600">
                                            Replace:
                                            <input type="file" name="photos[{{ $photo->id }}][file]" accept="image/jpeg,image/png,image/webp"
                                                   class="max-w-[14rem] text-xs file:mr-2 file:rounded file:border-0 file:bg-slate-100 file:px-2 file:py-1 file:text-xs file:font-semibold">
                                        </label>
                                    </div>
                                </div>

                                <div class="flex shrink-0 items-center gap-1">
                                    <button type="button" data-sort="up" aria-label="Move up" title="Move up" class="h-9 w-9 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-100 disabled:opacity-30">↑</button>
                                    <button type="button" data-sort="down" aria-label="Move down" title="Move down" class="h-9 w-9 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-100 disabled:opacity-30">↓</button>
                                    <button type="submit" form="delete-photo-{{ $photo->id }}" aria-label="Delete photo" title="Delete"
                                            onclick="return confirm('Delete this photo? This cannot be undone.')"
                                            class="h-9 w-9 rounded-lg border border-slate-200 text-red-600 hover:bg-red-50">✕</button>
                                </div>
                            </li>
                        @endforeach
                    </ol>

                    <div class="sticky bottom-0 -mx-6 mt-5 flex flex-wrap items-center gap-3 border-t border-slate-200 bg-white/95 px-6 py-4 backdrop-blur">
                        <button type="submit" class="inline-flex items-center rounded-lg bg-ink px-5 py-2.5 text-sm font-semibold text-gold-300 transition hover:bg-neutral-800">
                            Save photos
                        </button>
                        <span class="text-xs text-slate-500">Saves captions, published state, order and any replacement files.</span>
                    </div>
                </form>

                {{-- Delete forms live outside the main form (forms can't nest); the ✕ buttons target them. --}}
                @foreach ($photos as $photo)
                    <form method="POST" action="{{ route('admin.gallery.photos.destroy', $photo) }}" id="delete-photo-{{ $photo->id }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            @endif
        </div>
    </div>
@endsection
