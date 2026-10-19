// Photo gallery carousel + lightbox (resources/views/events/_gallery.blade.php).
// Slides hold eight photos each; arrows, dots and horizontal swipes change slide. Any photo
// opens a full-size lightbox (a native <dialog>: Esc and focus handling come for free) with
// previous/next, keyboard arrows and swipe.

const SWIPE_DISTANCE = 50; // px a finger must travel sideways to count as a swipe

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-gallery]').forEach(initGallery);
});

/** Calls back with -1/+1 for a mostly-horizontal swipe; vertical moves keep scrolling the page. */
function onSwipe(element, callback) {
    let start = null;
    element.addEventListener('touchstart', (event) => {
        start = event.touches.length === 1 ? { x: event.touches[0].clientX, y: event.touches[0].clientY } : null;
    }, { passive: true });
    element.addEventListener('touchend', (event) => {
        if (!start) return;
        const dx = event.changedTouches[0].clientX - start.x;
        const dy = event.changedTouches[0].clientY - start.y;
        start = null;
        if (Math.abs(dx) > SWIPE_DISTANCE && Math.abs(dx) > Math.abs(dy)) {
            callback(dx < 0 ? 1 : -1);
        }
    }, { passive: true });
}

function initGallery(root) {
    const track = root.querySelector('[data-gallery-track]');
    const slides = Array.from(root.querySelectorAll('[data-gallery-slide]'));
    const dots = Array.from(root.querySelectorAll('[data-gallery-dot]'));
    const prev = root.querySelector('[data-gallery-prev]');
    const next = root.querySelector('[data-gallery-next]');
    const photos = JSON.parse(root.querySelector('[data-gallery-photos]').textContent);
    let current = 0;

    const goTo = (index) => {
        current = Math.max(0, Math.min(slides.length - 1, index));
        track.style.transform = `translateX(-${current * 100}%)`;
        slides.forEach((slide, i) => {
            // Off-screen slides can't be tabbed into or read out.
            slide.inert = i !== current;
            slide.setAttribute('aria-hidden', String(i !== current));
        });
        dots.forEach((dot, i) => dot.setAttribute('aria-current', String(i === current)));
        if (prev) prev.disabled = current === 0;
        if (next) next.disabled = current === slides.length - 1;
    };

    prev?.addEventListener('click', () => goTo(current - 1));
    next?.addEventListener('click', () => goTo(current + 1));
    dots.forEach((dot) => dot.addEventListener('click', () => goTo(Number(dot.dataset.galleryDot))));
    onSwipe(root.querySelector('[data-gallery-viewport]'), (direction) => goTo(current + direction));
    goTo(0);

    // ---- Lightbox ----
    const dialog = root.querySelector('[data-gallery-lightbox]');
    const image = dialog.querySelector('[data-gallery-image]');
    const caption = dialog.querySelector('[data-gallery-caption]');
    const counter = dialog.querySelector('[data-gallery-counter]');
    let shown = 0;

    const show = (index) => {
        shown = (index + photos.length) % photos.length; // wraps around at either end
        const photo = photos[shown];
        image.src = photo.src;
        image.alt = photo.alt;
        caption.textContent = photo.caption || '';
        counter.textContent = `${shown + 1} / ${photos.length}`;
        // Preload the neighbours so stepping through feels instant.
        [shown - 1, shown + 1].forEach((i) => { new Image().src = photos[(i + photos.length) % photos.length].src; });
    };

    root.querySelectorAll('[data-gallery-open]').forEach((button) => {
        button.addEventListener('click', () => {
            show(Number(button.dataset.galleryOpen));
            dialog.showModal();
        });
    });

    dialog.querySelector('[data-gallery-close]').addEventListener('click', () => dialog.close());
    dialog.querySelector('[data-gallery-lightbox-prev]').addEventListener('click', () => show(shown - 1));
    dialog.querySelector('[data-gallery-lightbox-next]').addEventListener('click', () => show(shown + 1));
    dialog.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') show(shown - 1);
        if (event.key === 'ArrowRight') show(shown + 1);
    });
    // A click on the dark area around the photo closes it.
    dialog.querySelector('[data-gallery-stage]').addEventListener('click', (event) => {
        if (event.target === event.currentTarget) dialog.close();
    });
    onSwipe(dialog, (direction) => show(shown + direction));

    // Keep the carousel on the slide of the last photo viewed.
    dialog.addEventListener('close', () => goTo(Math.floor(shown / 8)));

    if (photos.length < 2) {
        dialog.querySelector('[data-gallery-lightbox-prev]').hidden = true;
        dialog.querySelector('[data-gallery-lightbox-next]').hidden = true;
    }
}
