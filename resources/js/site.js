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
    const countyFilter = document.getElementById('school-county-filter');
    const categoryFilter = document.getElementById('school-category-filter');
    const schoolRows = document.querySelectorAll('[data-school-row]');
    const emptyState = document.getElementById('school-empty-state');

    function applySchoolFilters() {
        const search = (searchInput?.value ?? '').trim().toLowerCase();
        const county = countyFilter?.value ?? '';
        const category = categoryFilter?.value ?? '';
        let visibleCount = 0;

        schoolRows.forEach((row) => {
            const matchesSearch = row.dataset.schoolName.includes(search);
            const matchesCounty = !county || row.dataset.schoolCounty === county;
            const matchesCategory = !category || row.dataset.schoolCategory === category;
            const isVisible = matchesSearch && matchesCounty && matchesCategory;

            row.classList.toggle('hidden', !isVisible);
            if (isVisible) visibleCount += 1;
        });

        emptyState?.classList.toggle('hidden', visibleCount > 0);
    }

    [searchInput, countyFilter, categoryFilter].forEach((control) => {
        control?.addEventListener('input', applySchoolFilters);
    });
});
