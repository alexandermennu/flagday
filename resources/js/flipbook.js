// Page-turning viewer for a published program PDF (resources/views/events/program-viewer.blade.php).
//
// PDF.js renders each page of the PDF to an image in the browser, and StPageFlip turns
// them like a book. Nothing here is specific to one event: the page supplies the PDF URL.
// The "legacy" PDF.js build is used for wider support on older phones.
import * as pdfjsLib from 'pdfjs-dist/legacy/build/pdf.mjs';
import PdfWorker from 'pdfjs-dist/legacy/build/pdf.worker.min.mjs?worker';
import { PageFlip } from 'page-flip/dist/js/page-flip.module.js';

// Bundled by Vite as a regular .js worker: some servers send .mjs files with a non-JavaScript
// MIME type, which browsers refuse to run as a worker.
pdfjsLib.GlobalWorkerOptions.workerPort = new PdfWorker();

const MAX_RENDER_WIDTH = 1600; // px per page — sharp on retina without exhausting phone memory

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
        const firstPage = await pdf.getPage(1);
        const { width: pageWidth, height: pageHeight } = firstPage.getViewport({ scale: 1 });

        const renderWidth = Math.min(MAX_RENDER_WIDTH, Math.ceil(book.clientWidth * Math.min(window.devicePixelRatio || 1, 2)));
        const images = [];

        for (let number = 1; number <= pdf.numPages; number++) {
            status.textContent = `Preparing page ${number} of ${pdf.numPages}…`;
            const page = number === 1 ? firstPage : await pdf.getPage(number);
            images.push(await renderPage(page, renderWidth, pageHeight / pageWidth));
            page.cleanup();
        }

        status.hidden = true;

        if (images.length === 1) {
            showSinglePage(book, images[0], root);
        } else {
            showBook(book, images, pageWidth, pageHeight, root);
        }
    } catch (exception) {
        console.error('Flipbook failed to load', exception);
        status.hidden = true;
        error.hidden = false;
    }
}

/**
 * Renders one PDF page to a JPEG object URL. Every page gets the same canvas size (the
 * first page's proportions), with any differently sized page centred on white, so the
 * book never stretches a page.
 */
async function renderPage(page, width, aspect) {
    const height = Math.round(width * aspect);
    const base = page.getViewport({ scale: 1 });
    const scale = Math.min(width / base.width, height / base.height);
    const viewport = page.getViewport({ scale });

    const pageCanvas = document.createElement('canvas');
    pageCanvas.width = Math.floor(viewport.width);
    pageCanvas.height = Math.floor(viewport.height);
    await page.render({ canvas: pageCanvas, viewport }).promise;

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const context = canvas.getContext('2d');
    context.fillStyle = '#ffffff';
    context.fillRect(0, 0, width, height);
    context.drawImage(pageCanvas, Math.round((width - pageCanvas.width) / 2), Math.round((height - pageCanvas.height) / 2));

    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.92));

    return URL.createObjectURL(blob);
}

function showBook(book, images, pageWidth, pageHeight, root) {
    const prev = root.querySelector('[data-flipbook-prev]');
    const next = root.querySelector('[data-flipbook-next]');
    const indicator = root.querySelector('[data-flipbook-indicator]');
    const total = images.length;

    const flip = new PageFlip(book, {
        width: Math.round(pageWidth), // proportions of one page; "stretch" scales to fit
        height: Math.round(pageHeight),
        size: 'stretch',
        minWidth: 280,
        maxWidth: 1000,
        minHeight: 200,
        // Keep a two-page spread within the screen height on desktop.
        maxHeight: Math.max(320, Math.round(window.innerHeight * 0.72)),
        showCover: total > 2, // front and back cover sit alone, like a printed booklet
        usePortrait: true, // one page at a time on narrow (mobile) screens
        mobileScrollSupport: false,
        maxShadowOpacity: 0.5,
        flippingTime: 900,
    });
    flip.loadFromImages(images);

    // StPageFlip's canvas renderer clears each frame to solid white, which paints a blank
    // "page" beside the lone front and back covers. Clear to transparent instead so the
    // dark stage shows through.
    const render = flip.getRender();
    render.clear = function () {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
    };

    const update = () => {
        const index = flip.getCurrentPageIndex();
        const spread = flip.getOrientation() === 'landscape';
        let first = index + 1;
        let last = first;

        if (spread) {
            // With covers shown alone, spreads are pages 2–3, 4–5, …
            const left = index === 0 ? 0 : (index % 2 === 1 ? index : index - 1);
            first = left + 1;
            last = left === 0 ? 1 : Math.min(left + 2, total);
        }

        indicator.textContent = first === last ? `Page ${first} of ${total}` : `Pages ${first}–${last} of ${total}`;
        prev.disabled = first <= 1;
        next.disabled = last >= total;
    };

    prev.addEventListener('click', () => flip.flipPrev());
    next.addEventListener('click', () => flip.flipNext());
    document.addEventListener('keydown', (event) => {
        if (event.target.closest('input, textarea, select')) return;
        if (event.key === 'ArrowLeft') flip.flipPrev();
        if (event.key === 'ArrowRight') flip.flipNext();
    });
    flip.on('flip', update);
    flip.on('changeOrientation', update);
    flip.on('init', update);

    root.querySelector('[data-flipbook-controls]').hidden = false;
    root.querySelector('[data-flipbook-hint]').hidden = false;
    update();
}

/** A one-page program (e.g. Baccalaureate) has nothing to turn — show the page itself. */
function showSinglePage(book, image, root) {
    const img = document.createElement('img');
    img.src = image;
    img.alt = 'Program page 1';
    img.className = 'mx-auto max-h-[72vh] w-auto rounded shadow-2xl shadow-black/60';
    book.appendChild(img);

    const indicator = root.querySelector('[data-flipbook-indicator]');
    indicator.textContent = 'Page 1 of 1';
    root.querySelector('[data-flipbook-prev]').disabled = true;
    root.querySelector('[data-flipbook-next]').disabled = true;
    root.querySelector('[data-flipbook-controls]').hidden = false;
}
