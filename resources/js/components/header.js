export default function initHeader() {
    const header = document.getElementById('app-header');
    if (!header) return;

    // --- State Management ---
    const state = {
        theme: localStorage.getItem('dnt_theme') || 'light', // Default to LIGHT based on user request
        openDropdown: null, // 'user' or 'notify' or null
        isDrawerOpen: false,
    };

    // --- DOM Elements ---
    const themeToggle = document.getElementById('theme-toggle');
    const html = document.documentElement;

    // Dropdowns
    const userBtn = document.getElementById('user-menu-btn');
    const userDropdown = document.getElementById('user-dropdown-container');
    const notifyBtn = document.getElementById('notify-btn');
    const notifyDropdown = document.getElementById('notify-dropdown-container');

    // Drawer
    const mobileToggle = document.getElementById('mobile-toggle');
    const closeDrawerBtn = document.getElementById('close-drawer');
    const drawerOverlay = document.getElementById('drawer-overlay');
    const drawer = document.getElementById('mobile-drawer');

    // --- Functions ---

    // Theme Logic
    function setTheme(theme) {
        state.theme = theme;
        html.setAttribute('data-theme', theme);
        localStorage.setItem('dnt_theme', theme);
        updateThemeIcon();
    }

    function toggleTheme() {
        setTheme(state.theme === 'dark' ? 'light' : 'dark');
    }

    function updateThemeIcon() {
        if (!themeToggle) return;
        const icon = themeToggle.querySelector('i');
        if (state.theme === 'dark') {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
    }

    // Dropdown Logic
    function closeAllDropdowns() {
        state.openDropdown = null;
        if (userDropdown) userDropdown.setAttribute('aria-expanded', 'false');
        if (notifyDropdown) notifyDropdown.setAttribute('aria-expanded', 'false');
    }

    function toggleDropdown(type) {
        const isOpen = state.openDropdown === type;
        closeAllDropdowns(); // Close others first
        
        if (!isOpen) {
            state.openDropdown = type;
            if (type === 'user' && userDropdown) userDropdown.setAttribute('aria-expanded', 'true');
            if (type === 'notify' && notifyDropdown) notifyDropdown.setAttribute('aria-expanded', 'true');
        }
    }

    // Drawer Logic
    function openDrawer() {
        state.isDrawerOpen = true;
        header.classList.add('drawer-open');
        document.body.classList.add('drawer-open');
        document.body.classList.add('scroll-locked');
        
        // Focus Trap Init
        const focusableElements = drawer.querySelectorAll('a[href], button, textarea, input[type="text"], input[type="radio"], input[type="checkbox"], select');
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (firstElement) firstElement.focus();

        drawer.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                if (e.shiftKey) { /* shift + tab */
                    if (document.activeElement === firstElement) {
                        e.preventDefault();
                        lastElement.focus();
                    }
                } else { /* tab */
                    if (document.activeElement === lastElement) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            }
        });
    }

    function closeDrawer() {
        state.isDrawerOpen = false;
        header.classList.remove('drawer-open');
        document.body.classList.remove('drawer-open');
        document.body.classList.remove('scroll-locked');
        // Return focus to toggle
        if (mobileToggle) mobileToggle.focus();
    }

    // --- Event Listeners ---

    // Theme
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
        updateThemeIcon();
    }

    // Dropdowns
    if (userBtn) {
        userBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleDropdown('user');
        });
    }

    if (notifyBtn) {
        notifyBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleDropdown('notify');
        });
    }

    // Click outside
    document.addEventListener('click', (e) => {
        if (state.openDropdown) {
            const target = e.target;
            const isInsideUser = userDropdown && userDropdown.contains(target);
            const isInsideNotify = notifyDropdown && notifyDropdown.contains(target);
            
            if (!isInsideUser && !isInsideNotify) {
                closeAllDropdowns();
            }
        }
    });

    // ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (state.openDropdown) closeAllDropdowns();
            if (state.isDrawerOpen) closeDrawer();
        }
    });

    // Drawer
    if (mobileToggle) mobileToggle.addEventListener('click', openDrawer);
    if (closeDrawerBtn) closeDrawerBtn.addEventListener('click', closeDrawer);
    if (drawerOverlay) drawerOverlay.addEventListener('click', closeDrawer);

    // Initial Setup
    setTheme(state.theme);
}
