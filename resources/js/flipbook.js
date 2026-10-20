// Page-turning viewer for a published program PDF (resources/views/events/program-viewer.blade.php).
//
// PDF.js renders each page of the PDF in the browser, and StPageFlip turns them like a
// book. Nothing here is specific to one event: the page supplies the PDF URL. The "legacy"
// PDF.js build is used for wider support on older phones.
//
// Sharpness: pages are rendered at exactly the size they are shown × the screen's pixel
// density, and StPageFlip's canvas is given a matching high-density backing store (it
// otherwise draws at 1 canvas pixel per CSS pixel, which blurs on every 2×/3× screen).
// Zooming shows the open pages in a separate layer, re-rendered straight from the PDF at
// the zoomed resolution — only for the visible pages, and only after the zoom settles.
import * as pdfjsLib from 'pdfjs-dist/legacy/build/pdf.mjs';
import PdfWorker from 'pdfjs-dist/legacy/build/pdf.worker.min.mjs?worker';
import { PageFlip } from 'page-flip/dist/js/page-flip.module.js';

// Bundled by Vite as a regular .js worker: some servers send .mjs files with a non-JavaScript
// MIME type, which browsers refuse to run as a worker.
pdfjsLib.GlobalWorkerOptions.workerPort = new PdfWorker();

const BOOK = { minWidth: 280, maxWidth: 1000 }; // StPageFlip sizing, shared with the estimate below
const MAX_BASE_WIDTH = 2560; // px per page for the book itself
const MAX_ZOOM_WIDTH = 4096; // px per page when zoomed in…
const MAX_ZOOM_PIXELS = 16_000_000; // …within mobile browsers' canvas size limits
const MIN_ZOOM = 1;
const MAX_ZOOM = 4;
const ZOOM_STEP = 1.5;

const pixelRatio = () => Math.min(window.devicePixelRatio || 1, 3);

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-flipbook]');
    if (root) {
        initFlipbook(root);
    }
});

async function initFlipbook(root) {
    const book = root.querySelector('[data-flipbook-book]');
    const status = root.querySelector('[data-flipbook-status]');
    const error = root.querySelector('[data-flipbook-error]');

    try {
        // Fetched in full (not streamed) so it works the same behind any proxy or CDN.
        const response = await fetch(root.dataset.pdfUrl, { credentials: 'same-origin' });
        if (!response.ok) {
            throw new Error(`Program request failed (${response.status})`);
        }

        const pdf = await pdfjsLib.getDocument({ data: new Uint8Array(await response.arrayBuffer()) }).promise;
        const { width: pageWidth, height: pageHeight } = (await pdf.getPage(1)).getViewport({ scale: 1 });
        const aspect = pageHeight / pageWidth;
        const maxHeight = Math.max(320, Math.round(window.innerHeight * 0.72));

        // Render at the size each page will actually be shown, at the screen's pixel density.
        const shownWidth = pdf.numPages === 1
            ? Math.min(book.clientWidth, maxHeight / aspect)
            : estimatePageWidth(book.clientWidth, pageWidth / pageHeight, maxHeight);
        const baseWidth = Math.min(MAX_BASE_WIDTH, Math.ceil(shownWidth * pixelRatio()));
        const images = [];

        for (let number = 1; number <= pdf.numPages; number++) {
            status.textContent = `Preparing page ${number} of ${pdf.numPages}…`;
            images.push(await renderPageImage(pdf, number, baseWidth, aspect));
        }

        status.hidden = true;

        const viewer = { root, book, pdf, aspect, baseWidth };
        if (images.length === 1) {
            showSinglePage(viewer, images[0], maxHeight);
        } else {
            showBook(viewer, images, pageWidth, pageHeight, maxHeight);
        }
    } catch (exception) {
        console.error('Flipbook failed to load', exception);
        status.hidden = true;
        error.hidden = false;
    }
}

/** The width (CSS px) StPageFlip will give one page — mirrors its "stretch" sizing. */
function estimatePageWidth(blockWidth, ratio, maxHeight) {
    const portrait = blockWidth < 2 * BOOK.minWidth;
    let width = Math.min(portrait ? blockWidth : blockWidth / 2, BOOK.maxWidth);
    if (width / ratio > maxHeight) {
        width = maxHeight * ratio;
    }

    return width;
}

/**
 * Renders a PDF page straight from the document into a canvas `width` px wide. Every page
 * gets the first page's proportions, with any differently sized page centred on white, so
 * the book never stretches a page.
 */
async function renderPageCanvas(pdf, number, width, aspect) {
    const page = await pdf.getPage(number);
    const height = Math.round(width * aspect);
    const base = page.getViewport({ scale: 1 });
    const viewport = page.getViewport({ scale: Math.min(width / base.width, height / base.height) });

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const context = canvas.getContext('2d');
    context.fillStyle = '#ffffff';
    context.fillRect(0, 0, width, height);

    await page.render({
        canvas,
        viewport,
        transform: [1, 0, 0, 1, Math.round((width - viewport.width) / 2), Math.round((height - viewport.height) / 2)],
    }).promise;
    page.cleanup();

    return canvas;
}

/** A page as a high-quality JPEG object URL, for StPageFlip's image pages. */
async function renderPageImage(pdf, number, width, aspect) {
    const canvas = await renderPageCanvas(pdf, number, width, aspect);
    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.95));

    return URL.createObjectURL(blob);
}

function showBook(viewer, images, pageWidth, pageHeight, maxHeight) {
    const { root, book, pdf, aspect } = viewer;
    const prev = root.querySelector('[data-flipbook-prev]');
    const next = root.querySelector('[data-flipbook-next]');
    const indicator = root.querySelector('[data-flipbook-indicator]');
    const total = images.length;
    const showCover = total > 2; // front and back cover sit alone, like a printed booklet

    const flip = new PageFlip(book, {
        width: Math.round(pageWidth), // proportions of one page; "stretch" scales to fit
        height: Math.round(pageHeight),
        size: 'stretch',
        minWidth: BOOK.minWidth,
        maxWidth: BOOK.maxWidth,
        minHeight: 200,
        // Keep a two-page spread within the screen height on desktop.
        maxHeight,
        showCover,
        usePortrait: true, // one page at a time on narrow (mobile) screens
        mobileScrollSupport: false,
        maxShadowOpacity: 0.5,
        flippingTime: 900,
        // Clicking the middle of a page no longer turns it, so double-click can zoom.
        // Clicking or dragging a page corner still turns the page.
        disableFlipByClick: true,
    });
    flip.loadFromImages(images);

    // StPageFlip's canvas renderer clears each frame to solid white, which paints a blank
    // "page" beside the lone front and back covers. Clear to transparent instead so the
    // dark stage shows through.
    const render = flip.getRender();
    render.clear = function () {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
    };

    // StPageFlip sizes its canvas at 1 pixel per CSS pixel, which the browser then stretches
    // (and blurs) on high-density screens. Give it a backing store at the screen's pixel
    // density and scale the drawing to match — its geometry is all in CSS pixels, so the
    // layout and the page-turn animation are unchanged.
    const ui = flip.getUI();
    const canvas = ui.getCanvas();
    ui.resizeCanvas = function () {
        const style = getComputedStyle(canvas);
        const ratio = pixelRatio();
        canvas.width = Math.round(parseFloat(style.width) * ratio);
        canvas.height = Math.round(parseFloat(style.height) * ratio);
        canvas.getContext('2d').setTransform(ratio, 0, 0, ratio, 0, 0);
    };
    ui.resizeCanvas();

    /** Indexes (0-based) of the pages currently open, as [left, right] in a spread. */
    const openPages = () => {
        const index = flip.getCurrentPageIndex();
        if (flip.getOrientation() === 'portrait') {
            return [null, index];
        }
        if (showCover && index === 0) {
            return [null, 0];
        }
        const left = showCover ? (index % 2 === 1 ? index : index - 1) : (index % 2 === 0 ? index : index - 1);

        return [left, left + 1 < total ? left + 1 : null];
    };

    const update = () => {
        const shown = openPages().filter((page) => page !== null);
        const first = shown[0] + 1;
        const last = shown[shown.length - 1] + 1;

        indicator.textContent = first === last ? `Page ${first} of ${total}` : `Pages ${first}–${last} of ${total}`;
        prev.disabled = first <= 1;
        next.disabled = last >= total;
    };

    // When the book grows (window resized, phone rotated), re-render the pages so they stay
    // sharp at the new size. Pages swap in one by one as they finish.
    let baseWidth = viewer.baseWidth;
    let resizeTimer;
    const sharpenForSize = async () => {
        const needed = Math.min(MAX_BASE_WIDTH, Math.ceil(render.getRect().pageWidth * pixelRatio()));
        if (needed <= baseWidth * 1.1) return;
        baseWidth = needed;

        for (let index = 0; index < total; index++) {
            const url = await renderPageImage(pdf, index + 1, needed, aspect);
            const image = new Image();
            image.onload = () => {
                const page = flip.getPageCollection().getPage(index);
                const previous = page.image.src;
                page.image = image;
                page.isLoad = true;
                URL.revokeObjectURL(previous);
            };
            image.src = url;
        }
    };
    window.addEventListener('resize', () => {
        zoom.reset();
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(sharpenForSize, 300);
    });

    const zoom = createZoom(viewer, () => {
        const rect = render.getRect();
        const [left, right] = openPages();

        return [[left, rect.left], [right, rect.left + rect.pageWidth]]
            .filter(([index]) => index !== null)
            .map(([index, x]) => ({
                number: index + 1,
                x,
                y: rect.top,
                width: rect.pageWidth,
                height: rect.height,
                image: flip.getPageCollection().getPage(index).image,
            }));
    });

    // Turning the page always returns to the normal (un-zoomed) book first.
    const turn = (direction) => {
        zoom.reset();
        direction < 0 ? flip.flipPrev() : flip.flipNext();
    };

    prev.addEventListener('click', () => turn(-1));
    next.addEventListener('click', () => turn(1));
    document.addEventListener('keydown', (event) => {
        if (event.target.closest('input, textarea, select')) return;
        if (event.key === 'ArrowLeft') turn(-1);
        if (event.key === 'ArrowRight') turn(1);
        if (event.key === 'Escape') zoom.reset();
    });
    flip.on('flip', update);
    flip.on('changeOrientation', update);
    flip.on('init', update);

    root.querySelector('[data-flipbook-controls]').hidden = false;
    root.querySelector('[data-flipbook-hint]').hidden = false;
    update();
    sharpenForSize();
}

/** A one-page program (e.g. Baccalaureate) has nothing to turn — show the page itself. */
function showSinglePage(viewer, image, maxHeight) {
    const { root, book } = viewer;
    const img = document.createElement('img');
    img.src = image;
    img.alt = 'Program page 1';
    img.className = 'mx-auto w-auto rounded shadow-2xl shadow-black/60';
    img.style.maxHeight = `${maxHeight}px`;
    book.appendChild(img);

    createZoom(viewer, () => {
        const frame = root.querySelector('[data-flipbook-frame]').getBoundingClientRect();
        const box = img.getBoundingClientRect();

        return [{ number: 1, x: box.left - frame.left, y: box.top - frame.top, width: box.width, height: box.height, image: img }];
    });

    root.querySelector('[data-flipbook-indicator]').textContent = 'Page 1 of 1';
    root.querySelector('[data-flipbook-prev]').disabled = true;
    root.querySelector('[data-flipbook-next]').disabled = true;
    root.querySelector('[data-flipbook-controls]').hidden = false;
    // Nothing to turn on a one-page program, so the hint only covers zooming.
    const hint = root.querySelector('[data-flipbook-hint]');
    hint.textContent = 'Double-click, pinch or use + to zoom; drag to move around.';
    hint.hidden = false;
}

/**
 * Zoom for the open pages: buttons, double-click/double-tap, pinch, Ctrl/⌘ + scroll (and
 * trackpad pinch), and drag or scroll to pan. While zoomed, a layer over the book shows
 * the open pages scaled uniformly (so proportions never change), first from the book's
 * own page images and then re-rendered from the PDF at the zoomed resolution.
 *
 * `getSlots()` returns the open pages' positions in the frame, in CSS pixels.
 */
function createZoom({ root, pdf, aspect }, getSlots) {
    const frame = root.querySelector('[data-flipbook-frame]');
    const layer = root.querySelector('[data-flipbook-zoom]');
    const content = root.querySelector('[data-flipbook-zoom-content]');
    const zoomIn = root.querySelector('[data-flipbook-zoom-in]');
    const zoomOut = root.querySelector('[data-flipbook-zoom-out]');
    const resetButton = root.querySelector('[data-flipbook-zoom-reset]');
    const level = root.querySelector('[data-flipbook-zoom-level]');

    let scale = 1;
    let x = 0;
    let y = 0;
    let slots = [];
    let bounds = null; // the open pages' outer edges, in unscaled frame coordinates
    let sharpenTimer;
    let generation = 0; // discards renders that finish after the zoom has moved on

    /**
     * Keeps the open pages in view: a scaled page wider (or taller) than the view can be
     * panned edge to edge but no further; a smaller one is centred. Empty stage beside a
     * lone cover is never panned into.
     */
    const clampAxis = (offset, view, start, end) => {
        const size = (end - start) * scale;
        return size <= view ? (view - size) / 2 - start * scale : Math.min(-start * scale, Math.max(view - end * scale, offset));
    };

    const apply = () => {
        if (bounds) {
            x = clampAxis(x, frame.clientWidth, bounds.left, bounds.right);
            y = clampAxis(y, frame.clientHeight, bounds.top, bounds.bottom);
        }
        content.style.transform = `translate(${x}px, ${y}px) scale(${scale})`;

        level.textContent = `${Math.round(scale * 100)}%`;
        zoomIn.disabled = scale >= MAX_ZOOM;
        zoomOut.disabled = scale <= MIN_ZOOM;
        resetButton.disabled = scale <= MIN_ZOOM;
    };

    const open = () => {
        content.style.width = `${frame.clientWidth}px`;
        content.style.height = `${frame.clientHeight}px`;
        content.replaceChildren();

        slots = getSlots().map((slot) => {
            const holder = document.createElement('div');
            holder.className = 'absolute overflow-hidden bg-white shadow-2xl shadow-black/60';
            Object.assign(holder.style, { left: `${slot.x}px`, top: `${slot.y}px`, width: `${slot.width}px`, height: `${slot.height}px` });

            // Show the book's own page image straight away; a sharper render replaces it.
            const canvas = document.createElement('canvas');
            canvas.width = slot.image.naturalWidth || slot.image.width;
            canvas.height = slot.image.naturalHeight || slot.image.height;
            canvas.getContext('2d').drawImage(slot.image, 0, 0);
            canvas.className = 'block h-full w-full';
            holder.appendChild(canvas);
            content.appendChild(holder);

            return { ...slot, holder, renderedWidth: canvas.width };
        });

        bounds = {
            left: Math.min(...slots.map((slot) => slot.x)),
            right: Math.max(...slots.map((slot) => slot.x + slot.width)),
            top: Math.min(...slots.map((slot) => slot.y)),
            bottom: Math.max(...slots.map((slot) => slot.y + slot.height)),
        };
        layer.hidden = false;
    };

    const close = () => {
        generation++;
        layer.hidden = true;
        content.replaceChildren();
        slots = [];
        bounds = null;
    };

    /** Re-render the open pages from the PDF at the current zoom, once it settles. */
    const scheduleSharpen = () => {
        clearTimeout(sharpenTimer);
        sharpenTimer = setTimeout(async () => {
            const current = ++generation;
            for (const slot of slots) {
                const byArea = Math.floor(Math.sqrt(MAX_ZOOM_PIXELS / aspect));
                const needed = Math.min(MAX_ZOOM_WIDTH, byArea, Math.ceil(slot.width * scale * pixelRatio()));
                if (needed <= slot.renderedWidth * 1.1) continue;

                const canvas = await renderPageCanvas(pdf, slot.number, needed, aspect);
                if (current !== generation) return;
                canvas.className = 'block h-full w-full';
                slot.holder.replaceChildren(canvas);
                slot.renderedWidth = needed;
            }
        }, 180);
    };

    /**
     * Zoom to `target`, keeping the point (px, py) in the frame where it is — by default the
     * middle of the open pages, so the zoom buttons never land on the empty side of a cover.
     */
    const zoomTo = (target, px, py) => {
        target = Math.min(MAX_ZOOM, Math.max(MIN_ZOOM, target));
        if (target <= MIN_ZOOM * 1.01) {
            reset();
            return;
        }
        if (scale === 1) {
            open();
        }
        if (px === undefined) {
            px = x + ((bounds.left + bounds.right) / 2) * scale;
            py = y + ((bounds.top + bounds.bottom) / 2) * scale;
        }

        x = px - ((px - x) * target) / scale;
        y = py - ((py - y) * target) / scale;
        scale = target;
        apply();
        scheduleSharpen();
    };

    const reset = () => {
        if (scale === 1 && layer.hidden) return;
        close(); // clears the page bounds first, so the reset transform isn't re-centred on them
        scale = 1;
        x = 0;
        y = 0;
        apply();
    };

    const point = (event) => {
        const box = frame.getBoundingClientRect();
        return [event.clientX - box.left, event.clientY - box.top];
    };

    zoomIn.addEventListener('click', () => zoomTo(scale * ZOOM_STEP));
    zoomOut.addEventListener('click', () => zoomTo(scale / ZOOM_STEP));
    resetButton.addEventListener('click', reset);

    // Double-click (desktop) zooms in at the pointer; at maximum zoom it resets.
    frame.addEventListener('dblclick', (event) => {
        event.preventDefault();
        scale >= MAX_ZOOM ? reset() : zoomTo(Math.max(2, scale * ZOOM_STEP), ...point(event));
    });

    // Ctrl/⌘ + scroll — and trackpad pinch, which browsers report the same way — zooms at
    // the pointer. Plain scrolling pans while zoomed, and scrolls the page otherwise.
    frame.addEventListener('wheel', (event) => {
        if (event.ctrlKey || event.metaKey) {
            event.preventDefault();
            zoomTo(scale * Math.exp(-event.deltaY * 0.01), ...point(event));
        } else if (scale > 1) {
            event.preventDefault();
            x -= event.deltaX;
            y -= event.deltaY;
            apply();
        }
    }, { passive: false });

    // Drag to pan while zoomed (mouse, pen, or one finger).
    let drag = null;
    layer.addEventListener('pointerdown', (event) => {
        if (!event.isPrimary) return;
        layer.setPointerCapture(event.pointerId);
        drag = { startX: event.clientX, startY: event.clientY, x, y };
    });
    layer.addEventListener('pointermove', (event) => {
        if (!drag || !event.isPrimary || pinch) return;
        x = drag.x + event.clientX - drag.startX;
        y = drag.y + event.clientY - drag.startY;
        apply();
    });
    const endDrag = () => { drag = null; };
    layer.addEventListener('pointerup', endDrag);
    layer.addEventListener('pointercancel', endDrag);

    // Pinch to zoom, and double-tap to zoom, on touch screens. Listening in the capture
    // phase on the frame lets a two-finger pinch start on the book itself without
    // StPageFlip treating it as a page-turn swipe.
    let pinch = null;
    let lastTap = null;
    const touchPoint = (touches) => {
        const box = frame.getBoundingClientRect();
        const [a, b] = touches;
        return {
            x: (a.clientX + b.clientX) / 2 - box.left,
            y: (a.clientY + b.clientY) / 2 - box.top,
            distance: Math.hypot(a.clientX - b.clientX, a.clientY - b.clientY),
        };
    };

    frame.addEventListener('touchstart', (event) => {
        if (event.touches.length !== 2) return;
        event.preventDefault();
        event.stopPropagation();
        const start = touchPoint(event.touches);
        if (scale === 1) open();
        pinch = { start, scale, x, y };
    }, { capture: true, passive: false });

    frame.addEventListener('touchmove', (event) => {
        if (!pinch || event.touches.length !== 2) return;
        event.preventDefault();
        event.stopPropagation();
        const now = touchPoint(event.touches);
        const target = Math.min(MAX_ZOOM, Math.max(MIN_ZOOM, pinch.scale * (now.distance / pinch.start.distance)));
        // Keep the content point that started under the fingers under them as they move.
        x = now.x - ((pinch.start.x - pinch.x) * target) / pinch.scale;
        y = now.y - ((pinch.start.y - pinch.y) * target) / pinch.scale;
        scale = target;
        apply();
    }, { capture: true, passive: false });

    frame.addEventListener('touchend', (event) => {
        if (pinch) {
            if (event.touches.length < 2) {
                pinch = null;
                scale <= MIN_ZOOM * 1.05 ? reset() : scheduleSharpen();
            }
            return;
        }

        // Double-tap: two quick taps close together.
        if (event.touches.length > 0 || event.changedTouches.length !== 1) return;
        const touch = event.changedTouches[0];
        const now = Date.now();
        if (lastTap && now - lastTap.time < 300 && Math.hypot(touch.clientX - lastTap.x, touch.clientY - lastTap.y) < 30) {
            event.preventDefault();
            lastTap = null;
            scale >= MAX_ZOOM ? reset() : zoomTo(Math.max(2, scale * ZOOM_STEP), ...point(touch));
        } else {
            lastTap = { time: now, x: touch.clientX, y: touch.clientY };
        }
    }, { capture: true, passive: false });

    apply();

    return { reset };
}
