export default function initProducts() {
    console.log('Product page initialized');

    // --- Mobile Filter Drawer ---
    const filterBtn = document.getElementById('cy-filter-toggle');
    const closeBtn = document.getElementById('cy-filter-close');
    const drawer = document.getElementById('cy-filter-drawer');
    const overlay = document.getElementById('cy-filter-overlay');

    function toggleDrawer(show) {
        if (show) {
            drawer?.classList.add('open');
            overlay?.classList.add('open');
            document.body.style.overflow = 'hidden';
            drawer?.setAttribute('aria-expanded', 'true');
        } else {
            drawer?.classList.remove('open');
            overlay?.classList.remove('open');
            document.body.style.overflow = '';
            drawer?.setAttribute('aria-expanded', 'false');
        }
    }

    filterBtn?.addEventListener('click', () => toggleDrawer(true));
    closeBtn?.addEventListener('click', () => toggleDrawer(false));
    overlay?.addEventListener('click', () => toggleDrawer(false));

    // Close on ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer?.classList.contains('open')) {
            toggleDrawer(false);
        }
    });

    // --- Auto Submit Sort ---
    const sortSelect = document.getElementById('cy-sort-select');
    const filterForm = document.getElementById('cy-filter-form');
    
    sortSelect?.addEventListener('change', () => {
        filterForm?.submit();
    });

    // --- View Toggle (Desktop) ---
    // Simple grid column switcher
    const gridContainer = document.getElementById('cy-product-grid');
    const viewBtns = document.querySelectorAll('.cy-view-btn');

    viewBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const cols = btn.dataset.cols;
            
            // Remove active state from all
            viewBtns.forEach(b => b.classList.remove('text-cyan-400', 'text-orange-500'));
            // Add active state (using simple color utility for now, or check theme)
            btn.classList.add(document.documentElement.getAttribute('data-theme') === 'light' ? 'text-orange-500' : 'text-cyan-400');

            // Reset classes
            if (!gridContainer) return;
            gridContainer.className = 'cy-product-list transition-all duration-300';
            
            // Apply new grid
            if (cols === '2') {
                gridContainer.classList.add('cols-2');
            } else if (cols === '3') {
                // Default md is 3
            } else if (cols === '4') {
                // Default lg is 4
            }
        });
    });

    // --- Debounce Search ---
    const searchInput = document.getElementById('cy-search-input');
    let timeout = null;

    searchInput?.addEventListener('input', (e) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            // Check if we are inside a form, if so submit it
            // Or if we want to update URL without reload (SPA style), but requirement says "submit form"
            // For now, let's just submit the form
             filterForm?.submit();
        }, 500); // 500ms debounce
    });
}
