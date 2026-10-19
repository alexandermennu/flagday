{{--
    Photo gallery carousel (admin → Gallery). Expects $gallery with $photos already loaded:
    up to 16 published photos, eight per slide — 4 columns × 2 rows on desktop, 3 columns on
    tablets, 2 on phones. Arrows, dots and swipe change slides; a photo opens the lightbox.
    Behaviour: resources/js/gallery.js.
--}}
@php
    $slides = $photos->chunk(\App\Models\Gallery::PER_SLIDE)->values();
    $lightbox = $photos->values()->map(fn ($photo, $i) => [
        'src' => $photo->url('full'),
        'caption' => $photo->caption,
        'alt' => $photo->caption ?: $gallery->title.' — photo '.($i + 1),
    ]);
@endphp

@push('head')
    @vite('resources/js/gallery.js')
@endpush

<section class="bg-white py-14 sm:py-20" data-gallery aria-roledescription="carousel" aria-label="{{ $gallery->title }}">
    <div class="mx-auto max-w-5xl px-5 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="font-display text-3xl font-semibold text-ink">{{ $gallery->title }}</h2>
                @if ($gallery->subtitle)
                    <p class="mt-2 max-w-2xl text-neutral-600">{{ $gallery->subtitle }}</p>
                @endif
            </div>

            @if ($slides->count() > 1)
                <div class="flex gap-2">
                    <button type="button" data-gallery-prev aria-label="Previous photos"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-neutral-300 text-ink transition hover:border-ink hover:bg-ink hover:text-white disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:border-neutral-300 disabled:hover:bg-transparent disabled:hover:text-ink">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg>
                    </button>
                    <button type="button" data-gallery-next aria-label="Next photos"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-neutral-300 text-ink transition hover:border-ink hover:bg-ink hover:text-white disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:border-neutral-300 disabled:hover:bg-transparent disabled:hover:text-ink">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                    </button>
                </div>
            @endif
        </div>

        <div class="mt-8 overflow-hidden" data-gallery-viewport style="touch-action: pan-y">
            <div class="flex transition-transform duration-500 ease-out motion-reduce:transition-none" data-gallery-track>
                @foreach ($slides as $s => $slide)
                    <div class="grid w-full shrink-0 grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-4"
                         data-gallery-slide role="group" aria-roledescription="slide" aria-label="{{ $s + 1 }} of {{ $slides->count() }}">
                        @foreach ($slide as $i => $photo)
                            {{-- chunk() keeps the original keys, so $i is the photo's index in the whole gallery --}}
                            <button type="button" data-gallery-open="{{ $i }}"
                                    class="group relative block aspect-[4/3] overflow-hidden rounded-lg bg-neutral-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500 focus-visible:ring-offset-2">
                                <img src="{{ $photo->url('thumb') }}" alt="{{ $lightbox[$i]['alt'] }}" width="800" height="600" loading="lazy" decoding="async"
                                     class="h-full w-full object-cover transition duration-500 group-hover:scale-105 motion-reduce:transition-none">
                                @if ($photo->caption)
                                    <span class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/75 to-transparent px-3 pb-2 pt-8 text-left text-xs font-medium text-white opacity-0 transition group-hover:opacity-100 group-focus-visible:opacity-100">
                                        {{ $photo->caption }}
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        @if ($slides->count() > 1)
            <div class="mt-6 flex justify-center gap-2">
                @foreach ($slides as $s => $slide)
                    <button type="button" data-gallery-dot="{{ $s }}" aria-label="Go to slide {{ $s + 1 }}"
                            class="h-2.5 w-2.5 rounded-full bg-neutral-300 transition-all aria-[current=true]:w-7 aria-[current=true]:bg-gold-500"></button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Lightbox --}}
    <dialog data-gallery-lightbox aria-label="{{ $gallery->title }}"
            class="m-0 h-full max-h-none w-full max-w-none bg-transparent p-0 text-white backdrop:bg-black/90">
        <div class="flex h-full flex-col">
            <div class="flex items-center justify-between px-4 py-3 sm:px-6">
                <p data-gallery-counter class="text-sm font-medium text-neutral-300" aria-live="polite"></p>
                <button type="button" data-gallery-close aria-label="Close"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/25 transition hover:border-gold-400 hover:text-gold-300">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" /></svg>
                </button>
            </div>
            <div class="relative flex min-h-0 flex-1 items-center justify-center px-4 sm:px-20" data-gallery-stage>
                <img data-gallery-image alt="" class="max-h-full max-w-full rounded-md object-contain shadow-2xl">
                <button type="button" data-gallery-lightbox-prev aria-label="Previous photo"
                        class="absolute left-2 top-1/2 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/25 bg-black/40 transition hover:border-gold-400 hover:text-gold-300 sm:left-5">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg>
                </button>
                <button type="button" data-gallery-lightbox-next aria-label="Next photo"
                        class="absolute right-2 top-1/2 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/25 bg-black/40 transition hover:border-gold-400 hover:text-gold-300 sm:right-5">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                </button>
            </div>
            <p data-gallery-caption class="min-h-12 px-6 py-4 text-center text-sm text-neutral-200"></p>
        </div>
    </dialog>

    <script type="application/json" data-gallery-photos>@json($lightbox)</script>
</section>
