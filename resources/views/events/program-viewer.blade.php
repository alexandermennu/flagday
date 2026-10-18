{{--
    Page-turning viewer for an event's published program. Works for any event: the page
    passes the PDF URL to resources/js/flipbook.js, which renders each page with PDF.js and
    turns them with StPageFlip. Without JavaScript (or if rendering fails) the visitor
    still gets links to open or download the original PDF.
--}}
@extends('layouts.public')

@section('title', $event['name'].' Program | '.config('graduation.institution').' Class of '.config('graduation.class_year'))
@section('description', 'The '.$event['name'].' program for the '.config('graduation.institution').' Class of '.config('graduation.class_year').'.')

@push('head')
    @vite('resources/js/flipbook.js')
@endpush

@section('content')
    <section class="relative isolate flex flex-1 flex-col overflow-hidden bg-ink text-white">
        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_50%_-10%,rgba(231,185,58,0.22),transparent_60%)]"></div>

        <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col px-5 py-6 sm:py-8 lg:px-8"
             data-flipbook
             data-pdf-url="{{ $event['program_url'] }}">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <a href="{{ route('events.show', $event['slug']) }}" class="text-sm font-medium text-neutral-400 transition hover:text-gold-300">
                        &larr; {{ $event['name'] }}
                    </a>
                    <h1 class="mt-1 font-display text-2xl font-semibold sm:text-3xl">
                        <span class="text-gold-gradient">{{ $event['name'] }} Program</span>
                    </h1>
                </div>
                <a href="{{ $event['program_download_url'] }}"
                   class="inline-flex items-center justify-center gap-2 rounded-full bg-gold-400 px-5 py-2.5 text-sm font-semibold text-ink transition hover:bg-gold-300">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10 2.5a.75.75 0 01.75.75v7.94l2.72-2.72a.75.75 0 111.06 1.06l-4 4a.75.75 0 01-1.06 0l-4-4a.75.75 0 111.06-1.06l2.72 2.72V3.25A.75.75 0 0110 2.5z" />
                        <path d="M3.5 13.25a.75.75 0 01.75.75v1.5c0 .14.11.25.25.25h11a.25.25 0 00.25-.25V14a.75.75 0 011.5 0v1.5A1.75 1.75 0 0115.5 17.25h-11A1.75 1.75 0 012.75 15.5V14a.75.75 0 01.75-.75z" />
                    </svg>
                    Download PDF
                </a>
            </div>

            {{-- The book. flipbook.js fills [data-flipbook-book] once the pages are rendered; the
                 zoom layer sits exactly over it and shows sharp, pannable copies of the open pages. --}}
            <div class="relative mt-6 flex flex-1 items-center justify-center" style="min-height: 18rem">
                <div data-flipbook-frame class="relative w-full">
                    <div data-flipbook-book class="w-full" aria-label="{{ $event['name'] }} program pages"></div>
                    <div data-flipbook-zoom hidden class="absolute inset-0 cursor-grab touch-none select-none overflow-hidden bg-ink active:cursor-grabbing">
                        <div data-flipbook-zoom-content class="absolute left-0 top-0 origin-top-left will-change-transform"></div>
                    </div>
                </div>

                <p data-flipbook-status role="status" class="absolute inset-0 flex items-center justify-center text-sm text-neutral-400">
                    Loading the program…
                </p>

                <div data-flipbook-error hidden class="absolute inset-0 flex flex-col items-center justify-center gap-4 text-center">
                    <p class="max-w-sm text-sm text-neutral-300">The page-turning viewer couldn't load on this device. You can still open or download the program.</p>
                    <div class="flex flex-wrap justify-center gap-3">
                        <a href="{{ $event['program_url'] }}" target="_blank" rel="noopener" class="rounded-full border border-white/25 px-5 py-2.5 text-sm font-semibold hover:border-gold-400 hover:text-gold-300">Open PDF</a>
                        <a href="{{ $event['program_download_url'] }}" class="rounded-full bg-gold-400 px-5 py-2.5 text-sm font-semibold text-ink hover:bg-gold-300">Download PDF</a>
                    </div>
                </div>

                <noscript>
                    <div class="absolute inset-0 flex flex-col items-center justify-center gap-4 bg-ink text-center">
                        <p class="text-sm text-neutral-300">Turn on JavaScript to flip through the program, or open the PDF directly.</p>
                        <a href="{{ $event['program_url'] }}" class="rounded-full bg-gold-400 px-5 py-2.5 text-sm font-semibold text-ink">Open PDF</a>
                    </div>
                </noscript>
            </div>

            {{-- Controls --}}
            <div class="mt-6 flex flex-wrap items-center justify-center gap-x-8 gap-y-4" data-flipbook-controls hidden>
            <div class="flex items-center justify-center gap-4">
                <button type="button" data-flipbook-prev aria-label="Previous page"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/25 text-white transition hover:border-gold-400 hover:text-gold-300 disabled:cursor-not-allowed disabled:opacity-30">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg>
                </button>
                <p data-flipbook-indicator aria-live="polite" class="min-w-32 text-center text-sm font-medium text-neutral-300"></p>
                <button type="button" data-flipbook-next aria-label="Next page"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/25 text-white transition hover:border-gold-400 hover:text-gold-300 disabled:cursor-not-allowed disabled:opacity-30">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                </button>
            </div>

            {{-- Zoom --}}
            <div class="flex items-center justify-center gap-2" role="group" aria-label="Zoom">
                <button type="button" data-flipbook-zoom-out aria-label="Zoom out"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/25 text-white transition hover:border-gold-400 hover:text-gold-300 disabled:cursor-not-allowed disabled:opacity-30">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4 10a.75.75 0 01.75-.75h10.5a.75.75 0 010 1.5H4.75A.75.75 0 014 10z" clip-rule="evenodd" /></svg>
                </button>
                <button type="button" data-flipbook-zoom-reset aria-label="Reset zoom"
                        class="inline-flex h-11 min-w-16 items-center justify-center rounded-full border border-white/25 px-3 text-sm font-medium tabular-nums text-neutral-300 transition hover:border-gold-400 hover:text-gold-300 disabled:cursor-default disabled:opacity-60">
                    <span data-flipbook-zoom-level>100%</span>
                </button>
                <button type="button" data-flipbook-zoom-in aria-label="Zoom in"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/25 text-white transition hover:border-gold-400 hover:text-gold-300 disabled:cursor-not-allowed disabled:opacity-30">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" /></svg>
                </button>
            </div>
            </div>
            <p class="mt-3 text-center text-xs text-neutral-500" data-flipbook-hint hidden>
                Drag or tap the page corners to turn pages, or use the arrow keys. Double-click, pinch or use + to zoom; drag to move around.
            </p>
        </div>
    </section>
@endsection
