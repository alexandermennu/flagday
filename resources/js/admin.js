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
