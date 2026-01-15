document.addEventListener('DOMContentLoaded', () => {
  const shell = document.querySelector('.admin-shell');
  const sidebar = document.getElementById('adminSidebar');
  const toggle = document.getElementById('adminSidebarToggle');
  const overlay = document.querySelector('[data-admin-overlay]');

  if (!shell || !sidebar || !toggle || !overlay) {
    return;
  }

  const openClass = 'sidebar-open';
  const body = document.body;

  const isDesktop = () => window.innerWidth >= 1024;

  const adjustAria = (open) => {
    if (isDesktop()) {
      sidebar.setAttribute('aria-hidden', 'false');
      return;
    }
    sidebar.setAttribute('aria-hidden', open ? 'false' : 'true');
  };

  const setState = (open) => {
    shell.classList.toggle(openClass, open);
    body.classList.toggle('admin-sidebar-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    adjustAria(open);
  };

  const closeSidebar = () => setState(false);
  const openSidebar = () => setState(true);

  setState(false);

  toggle.addEventListener('click', (event) => {
    event.preventDefault();
    if (isDesktop()) {
      return;
    }

    const currentlyOpen = shell.classList.contains(openClass);
    setState(!currentlyOpen);
  });

  overlay.addEventListener('click', closeSidebar);

  sidebar.addEventListener('click', (event) => {
    const link = event.target.closest('a');
    if (link && !isDesktop()) {
      closeSidebar();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
      return;
    }
    if (!shell.classList.contains(openClass)) {
      return;
    }
    closeSidebar();
  });

  window.addEventListener('resize', () => {
    adjustAria(shell.classList.contains(openClass));
    if (isDesktop()) {
      closeSidebar();
    }
  });
});
