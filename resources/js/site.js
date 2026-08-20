document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');

    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', () => {
            const isOpen = !mobileMenu.classList.contains('hidden');
            mobileMenu.classList.toggle('hidden', isOpen);
            menuToggle.setAttribute('aria-expanded', String(!isOpen));
        });
    }

    const searchInput = document.getElementById('school-search');
    const schoolRows = document.querySelectorAll('[data-school-row]');
    const emptyState = document.getElementById('school-empty-state');

    function applySchoolFilters() {
        const search = (searchInput?.value ?? '').trim().toLowerCase();
        let visibleCount = 0;

        schoolRows.forEach((row) => {
            const isVisible = row.dataset.schoolName.includes(search);
            row.classList.toggle('hidden', !isVisible);
            if (isVisible) visibleCount += 1;
        });

        emptyState?.classList.toggle('hidden', visibleCount > 0);
    }

    searchInput?.addEventListener('input', applySchoolFilters);
});
