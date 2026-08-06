document.addEventListener('DOMContentLoaded', () => {
    const TRANSITION_MS = 300;

    function expand(el) {
        if (!el || !el.classList.contains('hidden')) return;
        el.classList.remove('hidden');
        const targetHeight = el.scrollHeight;
        el.style.overflow = 'hidden';
        el.style.maxHeight = '0px';
        el.style.opacity = '0';
        requestAnimationFrame(() => {
            el.style.transition = `max-height ${TRANSITION_MS}ms ease, opacity ${TRANSITION_MS}ms ease`;
            el.style.maxHeight = `${targetHeight}px`;
            el.style.opacity = '1';
        });
        window.setTimeout(() => {
            el.style.maxHeight = '';
            el.style.overflow = '';
        }, TRANSITION_MS);
    }

    function collapse(el) {
        if (!el || el.classList.contains('hidden')) return;
        const startHeight = el.scrollHeight;
        el.style.overflow = 'hidden';
        el.style.maxHeight = `${startHeight}px`;
        el.style.opacity = '1';
        requestAnimationFrame(() => {
            el.style.transition = `max-height ${TRANSITION_MS}ms ease, opacity ${TRANSITION_MS}ms ease`;
            el.style.maxHeight = '0px';
            el.style.opacity = '0';
        });
        window.setTimeout(() => {
            el.classList.add('hidden');
            el.style.maxHeight = '';
            el.style.overflow = '';
        }, TRANSITION_MS);
    }

    function setBlockEnabled(block, enabled) {
        if (!block) return;
        block.querySelectorAll('input, select, textarea').forEach((field) => {
            field.disabled = !enabled;
        });
    }

    function syncSharedFields(fromBlock, toBlock) {
        if (!fromBlock || !toBlock) return;
        fromBlock.querySelectorAll('[data-sync-field]').forEach((source) => {
            const target = toBlock.querySelector(`[data-sync-field="${source.dataset.syncField}"]`);
            if (target && !target.value) {
                target.value = source.value;
            }
        });
    }

    /* ---------- Step 1: Attendance ---------- */

    const attendanceRadios = document.querySelectorAll('input[name="status"]');
    const yesBlock = document.getElementById('attend-yes-block');
    const noBlock = document.getElementById('attend-no-block');
    const submitButton = document.getElementById('submit-button');

    // Initial enabled/disabled state matches whatever Blade rendered as visible
    // (relevant when re-showing the form after a validation error).
    setBlockEnabled(yesBlock, yesBlock && !yesBlock.classList.contains('hidden'));
    setBlockEnabled(noBlock, noBlock && !noBlock.classList.contains('hidden'));

    attendanceRadios.forEach((radio) => {
        radio.addEventListener('change', () => {
            if (!radio.checked) return;

            if (submitButton) {
                submitButton.classList.remove('hidden');
            }

            if (radio.value === 'confirmed') {
                syncSharedFields(noBlock, yesBlock);
                collapse(noBlock);
                setBlockEnabled(noBlock, false);
                expand(yesBlock);
                setBlockEnabled(yesBlock, true);
            } else {
                syncSharedFields(yesBlock, noBlock);
                collapse(yesBlock);
                setBlockEnabled(yesBlock, false);
                expand(noBlock);
                setBlockEnabled(noBlock, true);
            }
        });
    });

    /* ---------- Step 2 (Yes path only): Additional guests ---------- */

    const guestToggleRadios = document.querySelectorAll('[data-guest-toggle]');
    const guestCountField = document.getElementById('guest-count-field');
    const guestCountSelect = document.getElementById('guest_count_select');
    const guestCards = document.getElementById('guest-cards');

    if (!guestToggleRadios.length || !guestCountField || !guestCountSelect || !guestCards) {
        return;
    }

    function guestCardTemplate(index) {
        return `
            <div class="guest-card rounded-xl border border-slate-200 bg-slate-50 p-5" data-guest-index="${index}">
                <h3 class="mb-3 text-sm font-semibold text-slate-700">Guest ${index + 1}</h3>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Full Name <span class="text-red-600">*</span></label>
                        <input type="text" name="guests[${index}][full_name]"
                               class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Organization <span class="text-red-600">*</span></label>
                        <input type="text" name="guests[${index}][organization]"
                               class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Position <span class="text-red-600">*</span></label>
                        <input type="text" name="guests[${index}][position]"
                               class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-blue-950 focus:outline-none focus:ring-1 focus:ring-blue-950">
                    </div>
                </div>
            </div>
        `;
    }

    function renderGuestCards(count) {
        const existingCards = Array.from(guestCards.querySelectorAll('.guest-card'));

        for (let i = existingCards.length; i < count; i++) {
            guestCards.insertAdjacentHTML('beforeend', guestCardTemplate(i));
        }

        for (let i = existingCards.length - 1; i >= count; i--) {
            const card = guestCards.querySelector(`[data-guest-index="${i}"]`);
            if (card) card.remove();
        }
    }

    guestToggleRadios.forEach((radio) => {
        radio.addEventListener('change', () => {
            if (!radio.checked) return;

            if (radio.value === 'yes') {
                expand(guestCountField);
            } else {
                collapse(guestCountField);
                collapse(guestCards);
                guestCountSelect.value = '';
                renderGuestCards(0);
            }
        });
    });

    guestCountSelect.addEventListener('change', () => {
        const count = parseInt(guestCountSelect.value, 10) || 0;
        renderGuestCards(count);

        if (count > 0) {
            expand(guestCards);
        } else {
            collapse(guestCards);
        }
    });
});
