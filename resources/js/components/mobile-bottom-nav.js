(function () {
  // ===== Dropdown manager (data-dd-btn / data-dd-panel) =====
  const btns = document.querySelectorAll('[data-dd-btn]');
  const panels = document.querySelectorAll('[data-dd-panel]');

  const closeAll = () => panels.forEach(p => p.classList.remove('is-open'));

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-dd-btn]');
    if (!btn) {
      // click outside
      if (!e.target.closest('[data-dd-panel]')) closeAll();
      return;
    }

    const key = btn.getAttribute('data-dd-btn');
    const panel = document.querySelector(`[data-dd-panel="${key}"]`);
    if (!panel) return;

    const willOpen = !panel.classList.contains('is-open');
    closeAll();
    if (willOpen) panel.classList.add('is-open');
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAll();
  });

  // ===== Image fallback =====
  document.querySelectorAll('img.js-img-fallback').forEach(img => {
    img.addEventListener('error', () => {
      const fb = img.getAttribute('data-fallback-src');
      if (fb && img.src !== fb) img.src = fb;
    });
  });

  // ===== Theme toggle (html[data-theme]) =====
  const root = document.documentElement;
  const toggle = document.getElementById('themeToggle');
  const icon = document.getElementById('themeIcon');

  const applyTheme = (t) => {
    root.setAttribute('data-theme', t);
    if (icon) {
      icon.classList.remove('fa-sun', 'fa-moon');
      icon.classList.add(t === 'dark' ? 'fa-moon' : 'fa-sun');
    }
    localStorage.setItem('dnt_theme', t);
  };

  const saved = localStorage.getItem('dnt_theme');
  if (saved) applyTheme(saved);

  if (toggle) {
    toggle.addEventListener('click', () => {
      const cur = root.getAttribute('data-theme') || 'light';
      applyTheme(cur === 'dark' ? 'light' : 'dark');
    });
  }

  // ===== markRead stub (nếu bạn đã có endpoint thì giữ; chưa có thì sẽ không crash) =====
  window.markRead = async (id, alreadyRead) => {
    if (alreadyRead) return;
    try {
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      await fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token || '', 'Accept': 'application/json' }
      });
    } catch (e) {}
  };
})();
