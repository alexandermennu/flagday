document.addEventListener('DOMContentLoaded', () => {
    const selectAll = document.getElementById('select-all');
    const remindButton = document.getElementById('remind-button');
    const rowCheckboxes = () => document.querySelectorAll('.row-checkbox');

    const updateButtonState = () => {
        if (!remindButton) return;
        remindButton.disabled = !Array.from(rowCheckboxes()).some((checkbox) => checkbox.checked);
    };

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            rowCheckboxes().forEach((checkbox) => {
                checkbox.checked = selectAll.checked;
            });
            updateButtonState();
        });
    }

    document.addEventListener('change', (event) => {
        if (event.target.classList.contains('row-checkbox')) {
            updateButtonState();
        }
    });

    updateButtonState();
});

// Repeatable rows (program agenda items and award categories): add, remove, reorder.
// Field names are re-indexed after every change so the submitted arrays follow the
// on-screen order — e.g. agenda[0][title], agenda[1][title], …
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-repeater]').forEach((repeater) => {
        const list = repeater.querySelector('[data-repeater-list]');
        const template = repeater.querySelector('[data-repeater-template]');
        const key = repeater.dataset.repeater;

        const reindex = () => {
            const rows = Array.from(list.querySelectorAll(':scope > [data-repeater-row]'));
            rows.forEach((row, index) => {
                row.querySelectorAll('[name]').forEach((field) => {
                    field.name = field.name.replace(new RegExp(`^${key}\\[[^\\]]*\\]`), `${key}[${index}]`);
                });
                const number = row.querySelector('[data-repeater-number]');
                if (number) number.textContent = index + 1;
                row.querySelector('[data-repeater-move="up"]').disabled = index === 0;
                row.querySelector('[data-repeater-move="down"]').disabled = index === rows.length - 1;
            });
        };

        repeater.querySelector('[data-repeater-add]')?.addEventListener('click', () => {
            const fragment = template.content.cloneNode(true);
            const row = fragment.querySelector('[data-repeater-row]');
            list.appendChild(fragment);
            reindex();
            row.querySelector('input, textarea')?.focus();
        });

        list.addEventListener('click', (event) => {
            const button = event.target.closest('button');
            const row = button?.closest('[data-repeater-row]');
            if (!row) return;

            if (button.matches('[data-repeater-remove]')) {
                row.remove();
            } else if (button.dataset.repeaterMove === 'up' && row.previousElementSibling) {
                list.insertBefore(row, row.previousElementSibling);
            } else if (button.dataset.repeaterMove === 'down' && row.nextElementSibling) {
                list.insertBefore(row.nextElementSibling, row);
            } else {
                return;
            }

            reindex();
            if (row.isConnected) button.focus();
        });

        reindex();
    });
});

// Program Type selector: shows only the sections that apply to the chosen type. On the
// New Program page, switching type before anything has been typed reloads the form with
// that type's starting content instead.
document.addEventListener('DOMContentLoaded', () => {
    const select = document.querySelector('[data-program-type]');
    if (!select) return;

    let edited = false;
    select.form.addEventListener('input', (event) => {
        if (event.target !== select) edited = true;
    });

    select.addEventListener('change', () => {
        if (select.dataset.typeReload && !edited) {
            window.location = `${select.dataset.typeReload}?type=${encodeURIComponent(select.value)}`;
            return;
        }

        document.querySelectorAll('[data-program-types]').forEach((element) => {
            element.hidden = !element.dataset.programTypes.split(' ').includes(select.value);
        });
    });
});

// Reorderable lists (the gallery photo list): up/down buttons move a row; hidden order[]
// inputs then submit in the on-screen order. Also keeps a live "N of 16 published" count.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-sortable]').forEach((list) => {
        const counter = document.querySelector('[data-published-counter]');

        const refresh = () => {
            const rows = Array.from(list.querySelectorAll(':scope > [data-sortable-row]'));
            rows.forEach((row, index) => {
                const number = row.querySelector('[data-sortable-number]');
                if (number) number.textContent = index + 1;
                row.querySelector('[data-sort="up"]').disabled = index === 0;
                row.querySelector('[data-sort="down"]').disabled = index === rows.length - 1;
            });

            if (counter) {
                const max = Number(counter.dataset.max);
                const published = list.querySelectorAll('[data-publish-toggle]:checked').length;
                counter.textContent = `${published} of ${max} published${published > max ? ' — too many' : ''}`;
                counter.classList.toggle('bg-red-100', published > max);
                counter.classList.toggle('text-red-700', published > max);
            }
        };

        list.addEventListener('click', (event) => {
            const button = event.target.closest('[data-sort]');
            if (!button) return;
            const row = button.closest('[data-sortable-row]');

            if (button.dataset.sort === 'up' && row.previousElementSibling) {
                list.insertBefore(row, row.previousElementSibling);
            } else if (button.dataset.sort === 'down' && row.nextElementSibling) {
                list.insertBefore(row.nextElementSibling, row);
            }

            refresh();
            button.focus();
        });
        list.addEventListener('change', (event) => {
            if (event.target.matches('[data-publish-toggle]')) refresh();
        });

        refresh();
    });
});
