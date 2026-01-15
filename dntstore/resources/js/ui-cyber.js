  (() => {
    'use strict';

    const ready = (fn) => {
      if (document.readyState !== 'loading') {
        fn();
      } else {
        document.addEventListener('DOMContentLoaded', fn);
      }
    };

    // NAV scroll glow
    ready(() => {
      const nav = document.getElementById('cyberNav');
      if (!nav) return;

      const onScroll = () => nav.classList.toggle('cyber-nav-scrolled', window.scrollY > 20);
      window.addEventListener('scroll', onScroll, { passive: true });
      onScroll();
    });

    // CYBER HEADER INTERACTIONS (Refactored to Alpine.js)
    // Legacy vanilla logic removed in favor of Alpine component 'siteHeader'
    
    // Notification Alpine helper & Site Header
    document.addEventListener('alpine:init', () => {
      // Notification Logic
      Alpine.data('notifDropdown', (cfg) => ({
        open: false,
        readUrl: cfg.readUrl,
        csrf: cfg.csrf,

        markRead(id, alreadyRead) {
          if (alreadyRead) return;
          const url = this.readUrl.replace('__ID__', id);
          fetch(url, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': this.csrf,
              'Accept': 'application/json'
            }
          }).catch(() => {});
        }
      }));

      // Main Header Logic
      Alpine.data('siteHeader', (config = {}) => ({
        scrolled: false,
        mobileOpen: false,
        theme: localStorage.getItem('theme') || document.documentElement.getAttribute('data-theme') || 'dark',
        cartCount: config.cartCount || 0,
        activeDropdown: null,
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',

        init() {
          // Scroll listener
          this.scrolled = window.scrollY > 20;
          window.addEventListener('scroll', () => {
            this.scrolled = window.scrollY > 20;
          }, { passive: true });

          // Cart listeners
          window.addEventListener('cart:updated', (e) => this.cartCount = e.detail?.count ?? 0);
          window.addEventListener('cart:count', (e) => this.cartCount = e.detail?.count ?? 0);

          // Theme init
          this.applyTheme(this.theme);

          // Mobile menu scroll lock watcher
          this.$watch('mobileOpen', (value) => {
            if (value) {
              document.body.style.overflow = 'hidden';
              this.$nextTick(() => this.$refs.mobileMenu?.focus());
            } else {
              document.body.style.overflow = '';
            }
          });
        },

        // Theme Methods
        toggleTheme() {
          this.theme = this.theme === 'light' ? 'dark' : 'light';
          this.applyTheme(this.theme);
          this.syncThemeBackend(this.theme);
        },

        applyTheme(val) {
          document.documentElement.setAttribute('data-theme', val);
          localStorage.setItem('theme', val);
        },

        async syncThemeBackend(val) {
          try {
            await fetch('/set-theme', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
              },
              body: JSON.stringify({ theme: val }),
            });
          } catch (e) {
            console.error('Theme sync error', e);
          }
        },

        // Dropdown Methods
        toggleDropdown(name) {
          if (this.activeDropdown === name) {
            this.activeDropdown = null;
          } else {
            this.activeDropdown = name;
          }
        },

        closeDropdown() {
          this.activeDropdown = null;
        },

        // Mobile Methods
        toggleMobile() {
          this.mobileOpen = !this.mobileOpen;
          if (this.mobileOpen) this.closeDropdown();
        },

        closeMobile() {
          this.mobileOpen = false;
        },
        
        // Focus Trap (Simple version)
        trapFocus(e) {
          if (!this.mobileOpen) return;
          const focusableElements = this.$refs.mobilePanel?.querySelectorAll('a[href], button, textarea, input[type="text"], input[type="radio"], input[type="checkbox"], select');
          if (!focusableElements || focusableElements.length === 0) return;
          
          const firstElement = focusableElements[0];
          const lastElement = focusableElements[focusableElements.length - 1];

          if (e.shiftKey) { 
            if (document.activeElement === firstElement) {
              lastElement.focus();
              e.preventDefault();
            }
          } else { 
            if (document.activeElement === lastElement) {
              firstElement.focus();
              e.preventDefault();
            }
          }
        }
      }));
    });

    // CIRCUIT CANVAS
    ready(() => {
      const canvas = document.getElementById('circuitCanvas');
      const header = document.getElementById('cyberNav');
      if (!canvas) return;
      const ctx = canvas.getContext('2d');
      let dpr = 1, vw = 0, vh = 0;

      const mouse = { x: -9999, y: -9999 };
      const clicks = [];
      const pulses = [];

      function resize() {
        dpr = Math.max(1, window.devicePixelRatio || 1);
        vw = window.innerWidth;
        vh = window.innerHeight;

        canvas.width = Math.floor(vw * dpr);
        canvas.height = Math.floor(vh * dpr);
        canvas.style.width = vw + 'px';
        canvas.style.height = vh + 'px';

        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.scale(dpr, dpr);
      }
      window.addEventListener('resize', resize, { passive: true });
      resize();

      window.addEventListener('mousemove', (e) => {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
      }, { passive: true });

      window.addEventListener('mousedown', (e) => {
        clicks.push({ x: e.clientX, y: e.clientY, r: 0, v: 9.5, life: 1.0 });
        for (let i = 0; i < 5; i++) {
          pulses.push({
            x: e.clientX + (Math.random() - 0.5) * 180,
            y: e.clientY + (Math.random() - 0.5) * 180,
            r: 0, v: 6 + Math.random() * 2.5, w: 26 + Math.random() * 18, life: 1.0
          });
        }
      });

      const TOTAL = 90;
      const HEADER_GROUP = 20;
      const nodes = Array.from({ length: TOTAL }, (_, i) => ({
        x: Math.random() * window.innerWidth,
        y: Math.random() * window.innerHeight,
        dir: Math.random() > 0.5 ? 'h' : 'v',
        speed: Math.random() * 0.55 + 0.18,
        pulse: Math.random() * 100,
        isHeader: i < HEADER_GROUP,
        hy: 0,
        hx: Math.random() * window.innerWidth
      }));

      function headerBottom() {
        if (!header) return 72;
        return header.getBoundingClientRect().bottom;
      }

      function nearestNode(x, y) {
        let best = 0;
        let bestD = 1e9;
        for (let i = 0; i < nodes.length; i++) {
          const dx = nodes[i].x - x;
          const dy = nodes[i].y - y;
          const d2 = dx * dx + dy * dy;
          if (d2 < bestD) {
            bestD = d2;
            best = i;
          }
        }
        return { idx: best, d: Math.sqrt(bestD) };
      }

      let hoverCooldown = 0;
      function maybeSpawnHoverPulse() {
        if (hoverCooldown > 0) {
          hoverCooldown--;
          return;
        }
        const { idx, d } = nearestNode(mouse.x, mouse.y);
        const strength = Math.max(0, 1 - d / 160);
        if (strength > 0.35) {
          pulses.push({
            x: nodes[idx].x,
            y: nodes[idx].y,
            r: 0,
            v: 5.2 + strength * 3,
            w: 22 + strength * 20,
            life: 0.95
          });
          hoverCooldown = Math.floor(10 - strength * 7);
        }
      }

      function clamp01(v) {
        return Math.max(0, Math.min(1, v));
      }

      function draw() {
        ctx.clearRect(0, 0, vw, vh);

        const hb = headerBottom();
        const bandTop = hb + 8;
        const bandBottom = hb + 120;

        for (const n of nodes) {
          if (n.isHeader) {
            n.hx += (Math.random() * 0.8 - 0.4);
            if (n.hx < -80) n.hx = vw + 80;
            if (n.hx > vw + 80) n.hx = -80;

            if (!n.hy || n.hy < bandTop || n.hy > bandBottom) {
              n.hy = bandTop + Math.random() * (bandBottom - bandTop);
            } else {
              n.hy += (Math.random() * 0.6 - 0.3);
              n.hy = Math.max(bandTop, Math.min(bandBottom, n.hy));
            }

            n.x += (n.hx - n.x) * 0.045;
            n.y += (n.hy - n.y) * 0.065;
          }
        }

        maybeSpawnHoverPulse();

        for (let i = clicks.length - 1; i >= 0; i--) {
          clicks[i].r += clicks[i].v;
          clicks[i].life -= 0.018;
          if (clicks[i].life <= 0) clicks.splice(i, 1);
        }
        for (let i = pulses.length - 1; i >= 0; i--) {
          pulses[i].r += pulses[i].v;
          pulses[i].life -= 0.022;
          if (pulses[i].life <= 0) pulses.splice(i, 1);
        }

        const alphaThemeRaw = getComputedStyle(document.documentElement).getPropertyValue('--circuit-alpha');
        const alphaTheme = Math.max(0, Math.min(1, parseFloat(alphaThemeRaw) || 1));

        const brand = getComputedStyle(document.documentElement).getPropertyValue('--brand').trim() || '#00bfff';

        let r = 0, g = 191, b = 255;
        if (brand[0] === '#' && brand.length === 7) {
          r = parseInt(brand.slice(1, 3), 16);
          g = parseInt(brand.slice(3, 5), 16);
          b = parseInt(brand.slice(5, 7), 16);
        }

        for (const n of nodes) {
          n.pulse += 0.02;

          const mdx = n.x - mouse.x;
          const mdy = n.y - mouse.y;
          const md = Math.sqrt(mdx * mdx + mdy * mdy);
          const hover = clamp01(1 - md / 240);

          let prop = 0;
          for (const p of pulses) {
            const dx = n.x - p.x;
            const dy = n.y - p.y;
            const d = Math.sqrt(dx * dx + dy * dy);
            const band = 1 - Math.abs(d - p.r) / p.w;
            if (band > 0) prop += band * p.life;
          }
          prop = clamp01(prop);

          let ripple = 0;
          for (const c of clicks) {
            const dx = n.x - c.x;
            const dy = n.y - c.y;
            const d = Math.sqrt(dx * dx + dy * dy);
            const band = 1 - Math.abs(d - c.r) / 34;
            if (band > 0) ripple += band * c.life;
          }
          ripple = clamp01(ripple);

          const idle = Math.abs(Math.sin(n.pulse)) * 0.22;
          const glow = clamp01(idle + hover * 0.55 + prop * 0.95 + ripple * 0.85);

          ctx.strokeStyle = `rgba(${r},${g},${b},${(0.10 + glow * 0.70) * alphaTheme})`;
          ctx.lineWidth = 1.1 + glow * 2.2;

          ctx.beginPath();
          ctx.moveTo(n.x, n.y);
          if (n.dir === 'h') {
            ctx.lineTo(n.x + 70, n.y);
            ctx.lineTo(n.x + 70, n.y + 45);
            if (!n.isHeader) {
              n.x += n.speed;
              if (n.x > vw + 110) n.x = -140;
            }
          } else {
            ctx.lineTo(n.x, n.y + 70);
            ctx.lineTo(n.x + 45, n.y + 70);
            if (!n.isHeader) {
              n.y += n.speed;
              if (n.y > vh + 110) n.y = -140;
            }
          }
          ctx.stroke();

          ctx.fillStyle = `rgba(${r},${g},${b},${(0.12 + glow * 0.55) * alphaTheme})`;
          ctx.beginPath();
          ctx.arc(n.x, n.y, 2.0 + glow * 1.6, 0, Math.PI * 2);
          ctx.fill();
        }

        for (const c of clicks) {
          ctx.strokeStyle = `rgba(255,255,255,${0.05 + c.life * 0.25})`;
          ctx.lineWidth = 1.2 + (1 - c.life) * 2.2;
          ctx.beginPath();
          ctx.arc(c.x, c.y, c.r, 0, Math.PI * 2);
          ctx.stroke();
        }

        requestAnimationFrame(draw);
      }

      draw();
    });

    // THEME TOGGLE + Spotlight hover
    ready(() => {
      if (document.body && document.body.classList.contains('ui-admin')) return;

      const root = document.documentElement;
      const btn = document.getElementById('themeToggle');
      const icon = document.getElementById('themeIcon');

      if (btn && icon) {
        const saved = localStorage.getItem('theme');
        if (saved === 'dark' || saved === 'light') {
          root.setAttribute('data-theme', saved);
        } else {
          root.setAttribute('data-theme', root.getAttribute('data-theme') || 'light');
        }

        function syncIcon() {
          const theme = root.getAttribute('data-theme');
          icon.className = theme === 'light' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        }
        syncIcon();

        btn.addEventListener('click', () => {
          const current = root.getAttribute('data-theme');
          const next = current === 'light' ? 'dark' : 'light';

          root.setAttribute('data-theme', next);
          localStorage.setItem('theme', next);
          syncIcon();

          fetch('/set-theme', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ theme: next })
          }).then((res) => {
            if (res.ok) window.location.reload();
          }).catch((err) => console.error('Error setting theme:', err));
        });
      }

      const mainBox = document.querySelector('.cp-main');
      if (mainBox) {
        mainBox.addEventListener('mousemove', (e) => {
          const r = mainBox.getBoundingClientRect();
          const x = ((e.clientX - r.left) / r.width) * 100;
          const y = ((e.clientY - r.top) / r.height) * 100;
          mainBox.style.setProperty('--mx', x + '%');
          mainBox.style.setProperty('--my', y + '%');
        });
        mainBox.addEventListener('mouseleave', () => {
          mainBox.style.setProperty('--mx', '50%');
          mainBox.style.setProperty('--my', '40%');
        });
      }
    });

    // HOME: PRODUCT CARD HOVER 3D
    ready(() => {
      (function() {
        const cards = document.querySelectorAll('.prod-card');
        if (!cards.length) return;

        const clamp = (n, min, max) => Math.max(min, Math.min(max, n));

        cards.forEach(card => {
          const spin = card.querySelector('.prod-spin');
          if (!spin) return;

          let rafId = null;

          function setVarsFromEvent(e) {
            const r = card.getBoundingClientRect();
            const cx = r.left + r.width / 2;
            const cy = r.top + r.height / 2;

            const dx = (e.clientX - cx) / (r.width / 2);
            const dy = (e.clientY - cy) / (r.height / 2);

            const ry = clamp(dx * 14, -14, 14);
            const rx = clamp(-dy * 10, -10, 10);

            spin.style.setProperty('--ry', ry.toFixed(2) + 'deg');
            spin.style.setProperty('--rx', rx.toFixed(2) + 'deg');
          }

          card.addEventListener('mouseenter', () => {
            card.classList.add('is-hover');
            if (card.dataset.turntable === '1') card.classList.add('is-spin');
          });

          card.addEventListener('mousemove', (e) => {
            if (rafId) cancelAnimationFrame(rafId);
            rafId = requestAnimationFrame(() => setVarsFromEvent(e));
          }, { passive: true });

          card.addEventListener('mouseleave', () => {
            card.classList.remove('is-hover');
            card.classList.remove('is-spin');
            spin.style.setProperty('--ry', '0deg');
            spin.style.setProperty('--rx', '0deg');
          });
        });
      })();
    });

    // HOME: BANNER SLIDER
    ready(() => {
      document.addEventListener('DOMContentLoaded', function() {
        const slides = document.querySelectorAll('.banner-slide');
        const dots = document.querySelectorAll('.banner-dot');
        const prevBtn = document.querySelector('.banner-prev');
        const nextBtn = document.querySelector('.banner-next');

        if (slides.length === 0) return;

        let currentSlide = 0;
        let slideInterval;

        function showSlide(index) {
          slides.forEach(slide => slide.classList.remove('opacity-100'));
          slides.forEach(slide => slide.classList.add('opacity-0'));

          dots.forEach((dot, i) => {
            dot.classList.remove('bg-white');
            dot.classList.add('bg-white/50');
          });

          slides[index].classList.remove('opacity-0');
          slides[index].classList.add('opacity-100');

          dots[index].classList.remove('bg-white/50');
          dots[index].classList.add('bg-white');

          currentSlide = index;
        }

        function nextSlide() {
          const next = (currentSlide + 1) % slides.length;
          showSlide(next);
        }

        function prevSlide() {
          const prev = (currentSlide - 1 + slides.length) % slides.length;
          showSlide(prev);
        }

        function startAutoSlide() {
          slideInterval = setInterval(nextSlide, 5000);
        }

        function stopAutoSlide() {
          clearInterval(slideInterval);
        }

        if (nextBtn) {
          nextBtn.addEventListener('click', () => {
            stopAutoSlide();
            nextSlide();
            startAutoSlide();
          });
        }

        if (prevBtn) {
          prevBtn.addEventListener('click', () => {
            stopAutoSlide();
            prevSlide();
            startAutoSlide();
          });
        }

        dots.forEach((dot, index) => {
          dot.addEventListener('click', () => {
            stopAutoSlide();
            showSlide(index);
            startAutoSlide();
          });
        });

        startAutoSlide();
      });
    });

    // HOME: HERO SLIDER (BLOCK 1)
    ready(() => {
      (function() {
        const track = document.getElementById('hsTrack');
        const dotsRoot = document.getElementById('hsDots');
        const prevBtn = document.querySelector('.hs-prev');
        const nextBtn = document.querySelector('.hs-next');
        if (!track) return;

        const slides = Array.from(track.querySelectorAll('.hs-slide'));
        if (slides.length <= 1) {
          prevBtn && (prevBtn.style.display = 'none');
          nextBtn && (nextBtn.style.display = 'none');
          dotsRoot && (dotsRoot.style.display = 'none');
          return;
        }

        let i = slides.findIndex(s => s.classList.contains('is-active'));
        if (i < 0) i = 0;

        let timer = null;
        const interval = 4500;

        if (dotsRoot) dotsRoot.innerHTML = '';
        const dots = slides.map((_, idx) => {
          const b = document.createElement('button');
          b.type = 'button';
          b.className = 'hs-dot';
          b.addEventListener('click', () => go(idx, true));
          dotsRoot && dotsRoot.appendChild(b);
          return b;
        });

        function render() {
          slides.forEach((s, idx) => s.classList.toggle('is-active', idx === i));
          dots.forEach((d, idx) => d.classList.toggle('is-active', idx === i));
        }

        function go(nextIndex, userAction = false) {
          i = (nextIndex + slides.length) % slides.length;
          render();
          if (userAction) {
            stop();
            start();
          }
        }

        function start() { timer = setInterval(() => go(i + 1), interval); }
        function stop() { if (timer) clearInterval(timer); timer = null; }

        nextBtn && nextBtn.addEventListener('click', () => go(i + 1, true));
        prevBtn && prevBtn.addEventListener('click', () => go(i - 1, true));

        track.addEventListener('mouseenter', stop);
        track.addEventListener('mouseleave', start);

        render();
        start();
      })();
    });

    // HOME: HERO SLIDER (BLOCK 2)
    ready(() => {
      (function() {
        const track = document.getElementById('hsTrack');
        const dotsRoot = document.getElementById('hsDots');
        const prevBtn = document.querySelector('.hs-prev');
        const nextBtn = document.querySelector('.hs-next');

        if (!track) return;

        const slides = Array.from(track.querySelectorAll('.hs-slide'));
        if (!slides.length) return;

        let i = 0;
        let timer = null;
        const interval = 5000;

        const dots = slides.map((_, idx) => {
          const b = document.createElement('button');
          b.type = 'button';
          b.className = 'hs-dot';
          b.addEventListener('click', () => go(idx, true));
          dotsRoot.appendChild(b);
          return b;
        });

        function render() {
          slides.forEach((s, idx) => s.classList.toggle('is-active', idx === i));
          dots.forEach((d, idx) => d.classList.toggle('is-active', idx === i));
        }

        function go(nextIndex, userAction = false) {
          i = (nextIndex + slides.length) % slides.length;
          render();
          if (userAction) {
            stop();
            start();
          }
        }

        function start() { timer = setInterval(() => go(i + 1), interval); }
        function stop() { if (timer) clearInterval(timer); timer = null; }

        if (nextBtn) nextBtn.addEventListener('click', () => go(i + 1, true));
        if (prevBtn) prevBtn.addEventListener('click', () => go(i - 1, true));

        track.addEventListener('mouseenter', stop);
        track.addEventListener('mouseleave', start);

        render();
        start();
      })();
    });

    // CHAT WIDGET
    ready(() => {
      // Singleton guard - prevent multiple initializations
      const toggleBtn = document.getElementById('dntChatToggle');
      const panel = document.getElementById('dntChatPanel');
      const closeBtn = document.getElementById('dntChatClose');
      const sendBtn = document.getElementById('dntChatSend');
      const input = document.getElementById('dntChatInput');
      const messages = document.getElementById('dntChatMessages');
      const statusEl = document.getElementById('dntChatStatus');
      const countdownEl = document.getElementById('dntChatCountdown');
      const quickButtons = document.querySelectorAll('.dnt-chat-quick');

      const missing = [];
      if (!toggleBtn) missing.push('dntChatToggle');
      if (!panel) missing.push('dntChatPanel');
      if (!closeBtn) missing.push('dntChatClose');
      if (!sendBtn) missing.push('dntChatSend');
      if (!input) missing.push('dntChatInput');
      if (!messages) missing.push('dntChatMessages');
      if (!statusEl) missing.push('dntChatStatus');
      if (!countdownEl) missing.push('dntChatCountdown');
      if (missing.length) {
        console.error('[DNT Chat] Missing elements:', missing);
        return;
      }

      if (window.__dntChatInitialized) return;
      window.__dntChatInitialized = true;

      const setStatus = (text) => { statusEl.textContent = text; };

      const storageKey = 'dnt_chat_session_id';
      let sessionId = localStorage.getItem(storageKey) || '';

      let isSending = false;
      let pollInterval = null;
      let statusPollInterval = null;
      let lastMessageId = 0;
      let sessionStatus = 'pending';
      let hydrated = false;
      let hydrationPromise = null;
      let pollController = null;
      let pollToken = 0;

      const renderedMessageIds = new Set();
      const renderedIdempotencyKeys = new Set();

      let serverOffsetMs = 0;
      let pendingCloseAtMs = null;
      let countdownIntervalId = null;

      const clearCountdown = () => {
        pendingCloseAtMs = null;
        if (countdownIntervalId) {
          clearInterval(countdownIntervalId);
          countdownIntervalId = null;
        }
        countdownEl.classList.add('hidden');
        countdownEl.textContent = '';
      };

      const getServerNowMs = () => Date.now() + serverOffsetMs;

      const renderCountdown = () => {
        if (!pendingCloseAtMs) return;
        const remainingMs = pendingCloseAtMs - getServerNowMs();
        const remainingSec = Math.ceil(remainingMs / 1000);

        if (remainingSec > 60) {
          clearCountdown();
          return;
        }

        if (remainingSec > 0) {
          countdownEl.textContent = `Bạn không phản hồi, phiên chat sẽ kết thúc sau: ${remainingSec}s`;
          countdownEl.classList.remove('hidden');
          return;
        }

        countdownEl.textContent = 'Phiên chat đang kết thúc...';
        countdownEl.classList.remove('hidden');
        disableSendUI(true);
        if (countdownIntervalId) {
          clearInterval(countdownIntervalId);
          countdownIntervalId = null;
        }
        setTimeout(() => fetchSessionStatus().catch(() => {}), 1200);
      };

      const applySessionStatusPayload = (payload) => {
        if (!payload) return;

        if (payload.status === 'closed') {
          clearCountdown();
          disableSendUI(true);
          setStatus('Phiên chat đã kết thúc');
          return;
        }

        disableSendUI(false);

        const pendingCloseAt = payload.pending_close_at ? Date.parse(payload.pending_close_at) : null;
        const serverNow = payload.server_now ? Date.parse(payload.server_now) : null;
        if (!pendingCloseAt || !serverNow) {
          clearCountdown();
          return;
        }

        serverOffsetMs = serverNow - Date.now();
        pendingCloseAtMs = pendingCloseAt;

        const remainingMs = pendingCloseAtMs - getServerNowMs();
        const remainingSec = Math.ceil(remainingMs / 1000);

        if (remainingSec <= 60 && remainingSec > 0) {
          countdownEl.classList.remove('hidden');
          renderCountdown();
          if (!countdownIntervalId) {
            countdownIntervalId = setInterval(renderCountdown, 1000);
          }
          return;
        }

        clearCountdown();
      };

      const fetchSessionStatus = async () => {
        if (!sessionId) return;
        const { res, json } = await fetchJson(`/api/chat/session-status?session_key=${encodeURIComponent(sessionId)}`);
        if (!res.ok) return;
        applySessionStatusPayload(json);
      };

      const startStatusPolling = () => {
        if (statusPollInterval) clearInterval(statusPollInterval);
        statusPollInterval = setInterval(() => {
          fetchSessionStatus().catch(() => {});
        }, 5000);
      };

      const stopStatusPolling = () => {
        if (statusPollInterval) {
          clearInterval(statusPollInterval);
          statusPollInterval = null;
        }
        clearCountdown();
      };

      const cssEscape = (value) => {
        if (window.CSS && typeof window.CSS.escape === 'function') return window.CSS.escape(String(value));
        return String(value).replace(/["\\]/g, '\\$&');
      };

      const generateUUID = () => {
        try {
          if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
          }
        } catch (_) {}
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
          const r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
          return v.toString(16);
        });
      };

      const openPanel = async () => {
        panel.classList.remove('hidden');
        toggleBtn.setAttribute('aria-expanded', 'true');

        // Start polling if we have a session
        if (sessionId) {
          await hydrateSessionOnce().catch(() => {});
          startPolling();
          fetchSessionMeta(); // Update status on open
          fetchSessionStatus().catch(() => {});
          startStatusPolling();
        }
      };

      const closePanel = () => {
        panel.classList.add('hidden');
        toggleBtn.setAttribute('aria-expanded', 'false');

        // Stop polling when panel is closed
        stopPolling();
        stopStatusPolling();
      };

      const addBubble = (role, text, messageId = null, idempotencyKey = null) => {
        const numericId = messageId ? Number(messageId) : null;
        const key = idempotencyKey ? String(idempotencyKey) : null;

        if (numericId && renderedMessageIds.has(numericId)) return;

        if (key && renderedIdempotencyKeys.has(key)) {
          const existing = messages.querySelector(`[data-idempotency-key="${cssEscape(key)}"]`);
          if (existing && numericId) {
            existing.dataset.messageId = String(numericId);
            renderedMessageIds.add(numericId);
          }
          return;
        }

        const wrap = document.createElement('div');
        wrap.className = role === 'user' ? 'flex justify-end' : 'flex justify-start';
        if (numericId) wrap.dataset.messageId = String(numericId);
        if (key) wrap.dataset.idempotencyKey = key;

        const bubble = document.createElement('div');
        if (role === 'user') {
          bubble.className = 'max-w-[85%] bg-sky-600 text-white rounded-2xl px-3 py-2 text-sm';
        } else if (role === 'admin') {
          bubble.className = 'max-w-[85%] bg-green-100 text-green-800 rounded-2xl px-3 py-2 text-sm whitespace-pre-line border border-green-200';
        } else {
          bubble.className = 'max-w-[85%] bg-gray-100 text-gray-800 rounded-2xl px-3 py-2 text-sm whitespace-pre-line';
        }
        bubble.textContent = text;

        wrap.appendChild(bubble);
        messages.appendChild(wrap);
        messages.scrollTop = messages.scrollHeight;

        if (numericId) renderedMessageIds.add(numericId);
        if (key) renderedIdempotencyKeys.add(key);
      };

      const clearMessages = () => {
        renderedMessageIds.clear();
        renderedIdempotencyKeys.clear();
        messages.innerHTML = '';
      };

      const fetchJson = async (url, options = {}, timeoutMs = 25000) => {
        const controller = new AbortController();
        const externalSignal = options?.signal;
        if (externalSignal) {
          if (externalSignal.aborted) controller.abort();
          else externalSignal.addEventListener('abort', () => controller.abort(), { once: true });
        }
        const t = setTimeout(() => controller.abort(), timeoutMs);

        try {
          const res = await fetch(url, { ...options, signal: controller.signal });
          const text = await res.text();
          let json = null;
          try { json = text ? JSON.parse(text) : null; } catch (_) {}
          return { res, text, json };
        } finally {
          clearTimeout(t);
        }
      };

      const disableSendUI = (disabled) => {
        sendBtn.disabled = disabled;
        input.disabled = disabled;
      };

      const rememberSession = (id) => {
        sessionId = id || '';
        if (sessionId) localStorage.setItem(storageKey, sessionId);
        else localStorage.removeItem(storageKey);
      };

      const startPolling = () => {
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(pollMessages, 2000);
      };

      const stopPolling = () => {
        if (pollController) {
          try { pollController.abort(); } catch (_) {}
          pollController = null;
        }
        if (pollInterval) {
          clearInterval(pollInterval);
          pollInterval = null;
        }
      };

      const pollMessages = async () => {
        if (!sessionId) return;
        if (pollController) return;

        try {
          const token = ++pollToken;
          pollController = new AbortController();
          const { res, json } = await fetchJson(
            `/api/chat/session/${sessionId}/poll?since=${lastMessageId}`,
            { signal: pollController.signal }
          );
          if (token !== pollToken) return;
          if (!res.ok) return;

          const data = json || {};
          if (Array.isArray(data.messages)) {
            data.messages.forEach(m => {
              addBubble(m.role || 'assistant', m.content || '', m.id, m.idempotency_key);
            });
          }

          // Use last_id from backend instead of calculating it
          if (data.last_id && data.last_id > lastMessageId) {
            lastMessageId = data.last_id;
          }

          if (data.session_status && data.session_status !== sessionStatus) {
            sessionStatus = data.session_status;
            updateStatusDisplay();
          }
        } catch (e) {
          console.warn('[DNT Chat] Poll error', e);
        } finally {
          pollController = null;
        }
      };

      const hydrateSessionOnce = async () => {
        if (!sessionId) return;
        if (hydrated) return;
        if (hydrationPromise) return hydrationPromise;

        hydrationPromise = (async () => {
          const { res, json } = await fetchJson(`/api/chat/session/${sessionId}/poll?since=0`);
          if (!res.ok) return;

          const data = json || {};
          clearMessages();

          if (Array.isArray(data.messages)) {
            data.messages.forEach(m => {
              addBubble(m.role || 'assistant', m.content || '', m.id, m.idempotency_key);
            });
          }

          if (data.last_id && data.last_id > lastMessageId) {
            lastMessageId = data.last_id;
          } else if (Array.isArray(data.messages) && data.messages.length) {
            const maxId = Math.max(...data.messages.map(m => Number(m?.id || 0)));
            if (maxId > lastMessageId) lastMessageId = maxId;
          }

          if (data.session_status) {
            sessionStatus = data.session_status;
            updateStatusDisplay();
          }

          hydrated = true;
        })().finally(() => {
          hydrationPromise = null;
        });

        return hydrationPromise;
      };

      const updateStatusDisplay = () => {
        if (sessionStatus === 'assigned') {
          setStatus('Đang được nhân viên hỗ trợ');
        } else if (sessionStatus === 'ai') {
          setStatus('AI đang hỗ trợ');
        } else {
          setStatus('Sẵn sàng');
        }
      };

      const fetchSessionMeta = async () => {
        if (!sessionId) return;

        try {
          const { res, json } = await fetchJson(`/api/chat/session/${sessionId}/meta`);
          if (res.ok) {
            const data = json || {};
            if (data.status && data.status !== sessionStatus) {
              sessionStatus = data.status;
              updateStatusDisplay();
            }
          }
        } catch (e) {
          console.warn('[DNT Chat] Meta fetch error', e);
        }
      };

      const startSession = async () => {
        const { res, text, json } = await fetchJson('/api/chat/session', {
          method: 'POST',
          headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify({})
        });

        if (!res.ok) {
          console.error('[DNT Chat] startSession error', res.status, text);
          throw new Error(`Cannot start session (${res.status})`);
        }

        const data = json || {};
        if (!data.session_id) throw new Error('Missing session_id from API');

        rememberSession(data.session_id);

        if (renderedMessageIds.size === 0 && renderedIdempotencyKeys.size === 0) {
          clearMessages();
        }

        if (Array.isArray(data.messages)) {
          data.messages.forEach(m => {
            // Pass id and idempotency_key for deduplication
            addBubble(m.role || 'assistant', m.content || '', m.id, m.idempotency_key);
          });
          const maxId = Math.max(...data.messages.map(m => Number(m?.id || 0)));
          if (maxId > lastMessageId) lastMessageId = maxId;
        }

        if (data.session_status) {
          sessionStatus = data.session_status;
          updateStatusDisplay();
        }

        hydrated = true;
      };

      const extractAssistantReply = (data) => {
        if (!data) return null;
        if (typeof data.reply_text === 'string' && data.reply_text.trim()) return data.reply_text;
        if (typeof data.reply === 'string' && data.reply.trim()) return data.reply;
        if (typeof data.message === 'string' && data.message.trim()) return data.message;
        if (data.assistant && typeof data.assistant === 'string') return data.assistant;

        if (Array.isArray(data.messages) && data.messages.length) {
          for (let i = data.messages.length - 1; i >= 0; i--) {
            const m = data.messages[i];
            if ((m.role || '').toLowerCase() !== 'user' && m.content) return m.content;
          }
          return data.messages[data.messages.length - 1]?.content || null;
        }
        return null;
      };

      const sendMessage = async () => {
        if (isSending) return;

        const text = (input.value || '').trim();
        if (!text) return;

        window.__dntChatSendInflight = window.__dntChatSendInflight || {};
        const inflightKey = sessionId ? `session:${sessionId}` : 'session:__new__';
        if (window.__dntChatSendInflight[inflightKey]) return;

        isSending = true;
        window.__dntChatSendInflight[inflightKey] = true;
        stopPolling();
        disableSendUI(true);
        quickButtons.forEach((b) => { b.disabled = true; });
        setStatus('Đang gửi...');

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
          const idempotencyKey = generateUUID();

          if (!sessionId) {
            await startSession();
          }

          const buildRequest = () => {
            const apiUrl = sessionStatus === 'assigned'
              ? `/api/chat/session/${sessionId}/message`
              : '/api/ai/chat';

            const payload = sessionStatus === 'assigned'
              ? { content: text, idempotency_key: idempotencyKey }
              : { session_id: sessionId, content: text, idempotency_key: idempotencyKey };

            return { apiUrl, payload };
          };

          let { apiUrl, payload } = buildRequest();

          let { res, text: raw, json } = await fetchJson(apiUrl, {
            method: 'POST',
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
              ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
            },
            body: JSON.stringify(payload)
          }, 15000);

          if (import.meta?.env?.DEV) {
            console.debug('[DNT Chat] send', { url: apiUrl, status: res.status, raw });
          }

          if (res.status === 404) {
            rememberSession('');
            await startSession();
            ({ apiUrl, payload } = buildRequest());
            ({ res, text: raw, json } = await fetchJson(apiUrl, {
              method: 'POST',
              headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
              },
              body: JSON.stringify(payload)
            }, 15000));
          }

          if (res.status === 400 || res.status === 422) {
            const data = json || {};
            const msg = String(data.message || '').toLowerCase();
            const hasSessionError = msg.includes('phiên chat') || msg.includes('kết thúc') || msg.includes('chuyển');
            const validationSession = data?.errors && data.errors.session_id;
            if (hasSessionError || validationSession) {
              rememberSession('');
              await startSession();
              ({ apiUrl, payload } = buildRequest());
              ({ res, text: raw, json } = await fetchJson(apiUrl, {
                method: 'POST',
                headers: {
                  'Accept': 'application/json',
                  'Content-Type': 'application/json',
                  ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                },
                body: JSON.stringify(payload)
              }, 15000));
            }
          }

          if (res.status === 429) {
            console.warn('[DNT Chat] rate limited 429', raw);
            addBubble('assistant', 'Hiện hệ thống đang quá tải (429). Bạn đợi 10–20 giây rồi gửi lại nhé.');
            setStatus('Quá tải');
            return;
          }

          if (!res.ok) {
            console.error('[DNT Chat] API error', res.status, raw);
            addBubble('assistant', `Xin lỗi, hệ thống lỗi API (${res.status}). Vui lòng thử lại.`);
            setStatus(`Lỗi API (${res.status})`);
            return;
          }

          const data = json || {};

          if (sessionStatus === 'assigned') {
            if (Array.isArray(data.messages) && data.messages.length) {
              data.messages.forEach(m => {
                addBubble(m.role || 'assistant', m.content || '', m.id, m.idempotency_key);
              });

              const maxId = Math.max(...data.messages.map(m => Number(m?.id || 0)));
              if (maxId > lastMessageId) lastMessageId = maxId;
            }
            updateStatusDisplay();
          } else {
            if (Array.isArray(data.messages) && data.messages.length) {
              data.messages.forEach(m => {
                addBubble(m.role || 'assistant', m.content || '', m.id, m.idempotency_key);
              });

              const maxId = Math.max(...data.messages.map(m => Number(m?.id || 0)));
              if (maxId > lastMessageId) lastMessageId = maxId;
            } else {
              const reply = extractAssistantReply(data) || 'Xin lỗi, hệ thống đang bận.';
              addBubble('assistant', reply);
            }

            try {
              const products = Array.isArray(data.products) ? data.products : [];
              if (products.length) {
                renderProductCards(products);
              }
            } catch (_) {}
            updateStatusDisplay();
          }

          input.value = '';
        } catch (e) {
          const isAbort = e && (e.name === 'AbortError');
          console.error('[DNT Chat] Network/Timeout error', e);
          addBubble('assistant', isAbort ? 'Hệ thống bận/timeout. Bạn thử lại sau 10–20 giây nhé.' : 'Không kết nối được máy chủ. Vui lòng thử lại.');
          setStatus('Sẵn sàng');
        } finally {
          isSending = false;
          disableSendUI(false);
          quickButtons.forEach((b) => { b.disabled = false; });
          window.__dntChatSendInflight[inflightKey] = false;
          input.focus();
          if (!panel.classList.contains('hidden') && sessionId) {
            await hydrateSessionOnce().catch(() => {});
            startPolling();
            fetchSessionStatus().catch(() => {});
            startStatusPolling();
          }
        }
      };

      function renderProductCards(products) {
        if (!messages) return;

        const wrap = document.createElement('div');
        wrap.className = 'flex justify-start';

        const container = document.createElement('div');
        container.className = 'w-full bg-white border rounded-2xl p-3 space-y-3';

        const grid = document.createElement('div');
        grid.className = 'grid grid-cols-2 gap-3';

        const csrf = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '';

        products.slice(0, 4).forEach(p => {
          console.log('CHAT_PRODUCT_DEBUG', p);
          const card = document.createElement('div');
          card.className = 'border rounded-xl overflow-hidden bg-gray-50';

          const img = document.createElement('img');
          img.src = p.image || '/image/bg.jpg';
          img.alt = p.name || 'Sản phẩm';
          img.className = 'w-full h-28 object-cover';
          card.appendChild(img);

          const body = document.createElement('div');
          body.className = 'p-2 space-y-1';

          const name = document.createElement('div');
          name.className = 'text-sm font-semibold line-clamp-2';
          name.textContent = p.name || 'Sản phẩm';
          body.appendChild(name);

          const price = document.createElement('div');
          price.className = 'text-sm text-sky-700';
          const toSafeNumber = (v) => {
            const n = Number(v ?? 0);
            return Number.isFinite(n) ? n : 0;
          };
          const sale = toSafeNumber(p.sale_price ?? p.salePrice ?? 0);
          const orig = toSafeNumber(p.original_price ?? p.originalPrice ?? 0);
          let priceVal = toSafeNumber(p.price ?? (sale > 0 ? sale : (orig > 0 ? orig : 0)));
          if (priceVal <= 0 && (sale > 0 || orig > 0)) {
            priceVal = sale > 0 ? sale : (orig > 0 ? orig : 0);
          }
          price.textContent = priceVal.toLocaleString('vi-VN') + ' đ';
          body.appendChild(price);

          const actions = document.createElement('div');
          actions.className = 'flex gap-2 mt-2';

          const viewBtn = document.createElement('a');
          viewBtn.href = p.url || '#';
          viewBtn.target = '_blank';
          viewBtn.className = 'flex-1 text-center text-xs px-2 py-1 rounded-md bg-white border';
          viewBtn.textContent = 'Xem';
          actions.appendChild(viewBtn);

          const addBtn = document.createElement('button');
          addBtn.type = 'button';
          addBtn.className = 'flex-1 text-center text-xs px-2 py-1 rounded-md bg-sky-600 text-white';
          addBtn.textContent = 'Thêm vào giỏ';
          addBtn.addEventListener('click', async () => {
            try {
              const res = await fetch(`/cart/add/${p.id}`, {
                method: 'POST',
                headers: {
                  'Accept': 'application/json',
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ qty: 1 })
              });
              if (res.ok) {
                showToast('Đã thêm vào giỏ');
              } else {
                showToast('Không thể thêm vào giỏ', true);
              }
            } catch (e) {
              showToast('Lỗi kết nối khi thêm vào giỏ', true);
            }
          });
          actions.appendChild(addBtn);

          body.appendChild(actions);
          card.appendChild(body);
          grid.appendChild(card);
        });

        container.appendChild(grid);
        wrap.appendChild(container);
        messages.appendChild(wrap);
        messages.scrollTop = messages.scrollHeight;
      }

      function formatCurrency(vnd) {
        try {
          return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(vnd);
        } catch {
          return (vnd || 0).toLocaleString('vi-VN') + ' ₫';
        }
      }

      function showToast(text, isError = false) {
        if (!messages) return;
        const wrap = document.createElement('div');
        wrap.className = 'flex justify-start';
        const bubble = document.createElement('div');
        bubble.className = isError
          ? 'max-w-[85%] bg-red-100 text-red-800 rounded-2xl px-3 py-2 text-sm'
          : 'max-w-[85%] bg-emerald-100 text-emerald-900 rounded-2xl px-3 py-2 text-sm';
        bubble.textContent = text;
        wrap.appendChild(bubble);
        messages.appendChild(wrap);
        messages.scrollTop = messages.scrollHeight;
      }

      toggleBtn.addEventListener('click', async () => {
        const expanded = toggleBtn.getAttribute('aria-expanded') === 'true';
        if (expanded) {
          closePanel();
          return;
        }

        await openPanel();
      });

      closeBtn.addEventListener('click', closePanel);
      sendBtn.addEventListener('click', sendMessage);
      quickButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
          const preset = btn.getAttribute('data-chat-preset') || '';
          if (!preset) return;
          input.value = preset;
          sendMessage();
        });
      });

      input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.repeat && !e.isComposing) {
          e.preventDefault();
          sendMessage();
        }
      });
    });

    // CONTACT
    ready(() => {
      if (document.body.getAttribute('data-page') !== 'contact') return;

      const form = document.getElementById('contactForm');
      const btn = document.getElementById('submitBtn');
      const toast = document.getElementById('toast');
      const copyBtn = document.querySelector('.js-contact-copy');

      form?.addEventListener('submit', () => {
        if (!btn) return;
        btn.disabled = true;
        btn.textContent = 'Đang gửi...';
      });

      function showToast(msg) {
        if (!toast) return;
        toast.textContent = msg;
        toast.style.display = 'block';
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => {
          toast.style.display = 'none';
        }, 1500);
      }

      async function copyAddress() {
        const text = '24/25 Nguyễn Sáng, Phường Tây Thạnh, Quận Tân Phú, TP. Hồ Chí Minh';
        if (navigator.clipboard && window.isSecureContext) {
          try {
            await navigator.clipboard.writeText(text);
            showToast('Đã copy địa chỉ!');
          } catch (_) {}
          return;
        }
        const t = document.createElement('textarea');
        t.value = text;
        document.body.appendChild(t);
        t.select();
        document.execCommand('copy');
        document.body.removeChild(t);
        showToast('Đã copy địa chỉ!');
      }

      copyBtn?.addEventListener('click', copyAddress);

      (function(){
        const canvas = document.querySelector('.wrap #circuitCanvas');
        if(!canvas) return;
        const ctx = canvas.getContext('2d');

        let dpr = Math.max(1, window.devicePixelRatio || 1);

        function resize(){
          dpr = Math.max(1, window.devicePixelRatio || 1);
          canvas.width  = Math.floor(window.innerWidth * dpr);
          canvas.height = Math.floor(window.innerHeight * dpr);
          canvas.style.width  = window.innerWidth + 'px';
          canvas.style.height = window.innerHeight + 'px';
          ctx.setTransform(1,0,0,1,0,0);
          ctx.scale(dpr, dpr);
        }
        window.addEventListener('resize', resize, { passive:true });
        resize();

        const N = 85;
        const nodes = Array.from({length:N}, () => ({
          x: Math.random() * window.innerWidth,
          y: Math.random() * window.innerHeight,
          dir: Math.random() > .5 ? 'h' : 'v',
          speed: Math.random() * 0.6 + 0.2,
          pulse: Math.random() * 100
        }));

        function draw(){
          ctx.clearRect(0,0,window.innerWidth, window.innerHeight);

          for(const n of nodes){
            n.pulse += 0.02;
            const glow = Math.abs(Math.sin(n.pulse)) * 0.8 + 0.2;

            ctx.strokeStyle = `rgba(34,211,238,${0.10 + glow*0.30})`;
            ctx.lineWidth = 1;

            ctx.beginPath();
            ctx.moveTo(n.x, n.y);

            if(n.dir === 'h'){
              ctx.lineTo(n.x + 72, n.y);
              ctx.lineTo(n.x + 72, n.y + 44);
              n.x += n.speed;
              if(n.x > window.innerWidth + 120) n.x = -140;
            }else{
              ctx.lineTo(n.x, n.y + 72);
              ctx.lineTo(n.x + 44, n.y + 72);
              n.y += n.speed;
              if(n.y > window.innerHeight + 120) n.y = -140;
            }
            ctx.stroke();

            ctx.fillStyle = `rgba(167,139,250,${0.20 + glow*0.55})`;
            ctx.beginPath();
            ctx.arc(n.x, n.y, 2.1, 0, Math.PI*2);
            ctx.fill();
          }

          requestAnimationFrame(draw);
        }
        draw();
      })();
    });

    // BOOKING HISTORY
    ready(() => {
      if (document.body.getAttribute('data-page') !== 'booking.history') return;
      const buttons = document.querySelectorAll('.js-booking-cancel');
      if (!buttons.length) return;

      let cancelLock = false;
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

      async function cancelBooking(id) {
        if (cancelLock) return;
        if (!confirm('Bạn có chắc chắn muốn hủy đặt lịch này?')) return;

        cancelLock = true;
        try {
          const res = await fetch(`/booking/${id}/cancel`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrf,
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            }
          });

          const ct = res.headers.get('content-type') || '';
          const data = ct.includes('application/json')
            ? await res.json()
            : { ok: false, message: 'Server không trả JSON (có thể đang redirect/lỗi).' };

          if (!res.ok || data.ok === false) {
            alert(data.message || 'Hủy thất bại. Vui lòng thử lại.');
            return;
          }

          alert(data.message || 'Đã hủy đặt lịch.');
          window.location.href = window.location.pathname;
        } catch (e) {
          alert('Có lỗi xảy ra. Vui lòng thử lại.');
        } finally {
          cancelLock = false;
        }
      }

      buttons.forEach((btn) => {
        btn.addEventListener('click', () => {
          const id = btn.getAttribute('data-booking-id');
          if (id) cancelBooking(id);
        });
      });
    });

    // SERVICES
    ready(() => {
      if (document.body.getAttribute('data-page') !== 'services') return;
      document.querySelectorAll('.svc-card').forEach(card => {
        let raf = null;

        card.addEventListener('mousemove', (e) => {
          if (raf) cancelAnimationFrame(raf);

          raf = requestAnimationFrame(() => {
            const r = card.getBoundingClientRect();
            const x = (e.clientX - r.left) / r.width;
            const y = (e.clientY - r.top) / r.height;

            const rx = (0.5 - y) * 6;
            const ry = (x - 0.5) * 8;

            card.style.transform = `translateY(-6px) rotateX(${rx}deg) rotateY(${ry}deg)`;
            card.style.setProperty('--mx', (x * 100) + '%');
            card.style.setProperty('--my', (y * 100) + '%');
          });
        });

        card.addEventListener('mouseleave', () => {
          if (raf) cancelAnimationFrame(raf);
          card.style.transform = '';
          card.style.setProperty('--mx','50%');
          card.style.setProperty('--my','40%');
        });
      });
    });

    // CLEARANCE SHOW
    ready(() => {
      if (document.body.getAttribute('data-page') !== 'clearance.show') return;

      const wrap = document.querySelector('.hud-wrap');
      const productId = wrap?.getAttribute('data-product-id');
      let variantsData = [];
      const variantsRaw = wrap?.getAttribute('data-variants') || '[]';
      try {
        variantsData = JSON.parse(variantsRaw);
      } catch (_) {
        variantsData = [];
      }

      let selectedVariantId = null;
      let selectedVariantKey = null;

      function switchImage(el){
        const main = document.getElementById('mainImage');
        const src = el.dataset.src;
        if(main && src) main.src = src;

        document.querySelectorAll('.thumb').forEach(t => t.classList.remove('is-active'));
        el.classList.add('is-active');
      }

      document.querySelectorAll('.js-thumb').forEach((thumb) => {
        thumb.addEventListener('click', () => switchImage(thumb));
      });

      const scrollBtn = document.querySelector('.js-scroll-cart');
      scrollBtn?.addEventListener('click', () => {
        const target = scrollBtn.getAttribute('data-target') || '#addToCartBtn';
        document.querySelector(target)?.scrollIntoView({ behavior:'smooth', block:'center' });
      });

      const hero = document.getElementById('heroBox');
      if(hero){
        hero.addEventListener('mousemove', (e) => {
          const r = hero.getBoundingClientRect();
          const x = ((e.clientX - r.left) / r.width) * 100;
          const y = ((e.clientY - r.top) / r.height) * 100;
          hero.style.setProperty('--mx', x + '%');
          hero.style.setProperty('--my', y + '%');
        });
        hero.addEventListener('mouseleave', () => {
          hero.style.setProperty('--mx','50%');
          hero.style.setProperty('--my','40%');
        });
      }

      const tabs = document.querySelectorAll('.tab');
      const panels = {
        desc: document.getElementById('tab-desc'),
        spec: document.getElementById('tab-spec'),
        policy: document.getElementById('tab-policy'),
        reviews: document.getElementById('tab-reviews'),
      };
      tabs.forEach(btn => {
        btn.addEventListener('click', () => {
          tabs.forEach(b => b.classList.remove('is-active'));
          btn.classList.add('is-active');

          Object.values(panels).forEach(p => p.classList.add('hidden'));
          panels[btn.dataset.tab]?.classList.remove('hidden');
        });
      });

      const prevBtn = document.getElementById('heroPrev');
      const nextBtn = document.getElementById('heroNext');
      const thumbs = Array.from(document.querySelectorAll('.thumb'));
      const getActiveIndex = () => thumbs.findIndex(t => t.classList.contains('is-active'));
      const go = (dir) => {
        if (!thumbs.length) return;
        const current = getActiveIndex();
        const next = current < 0 ? 0 : (current + dir + thumbs.length) % thumbs.length;
        switchImage(thumbs[next]);
      };
      prevBtn?.addEventListener('click', () => go(-1));
      nextBtn?.addEventListener('click', () => go(1));

      const btn = document.getElementById('addToCartBtn');
      if(!btn) return;

      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      const qtyInput = document.getElementById('qtyInput');
      const msg = document.getElementById('addMsg');
      const colorSelect = document.getElementById('colorSelect');
      const sizeSelect = document.getElementById('sizeSelect');
      const priceBox = document.getElementById('priceBox');
      const stockBox = document.getElementById('stockBox');
      const variantBox = document.getElementById('variantBox');
      const variantSelects = Array.from(document.querySelectorAll('.variant-select'));
      const hasSimpleVariants = variantSelects.length > 0;

      const variantIndex = new Map();
      if (hasSimpleVariants) {
        for (const v of variantsData) {
          const key = (v.pairs || []).map(p => `${p.name}:${p.value}`).sort().join('|');
          if (key) variantIndex.set(key, v);
        }
        btn.disabled = true;
      }

      function refreshSimpleVariant() {
        const selectedValues = {};
        variantSelects.forEach(sel => {
          selectedValues[sel.dataset.optionId] = sel.value || '';
        });
        const picked = Object.values(selectedValues).filter(Boolean);
        if (picked.length !== variantSelects.length) {
          selectedVariantKey = null;
          btn.disabled = true;
          return;
        }
        const key = Object.entries(selectedValues)
          .map(([optId, valId]) => `${optId}:${valId}`)
          .sort()
          .join('|');
        const v = variantIndex.get(key);
        if (v) {
          selectedVariantKey = key;
          selectedVariantId = null;
          const price = parseFloat(v.price ?? 0);
          if (priceBox) priceBox.textContent = new Intl.NumberFormat('vi-VN').format(price);
          btn.disabled = false;
        } else {
          selectedVariantKey = null;
          btn.disabled = true;
        }
      }

      async function refreshVariant(){
        const color = colorSelect?.value || '';
        const size = sizeSelect?.value || '';

        if (!color && !size) {
          selectedVariantId = null;
          btn.disabled = false;
          if(variantBox) variantBox.textContent = 'READY';
          return;
        }

        try{
          const url = `/api/products/${productId}/variant?color=${encodeURIComponent(color)}&size=${encodeURIComponent(size)}`;
          const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
          const data = await res.json();
          const v = data?.variant;

          if (v) {
            selectedVariantId = v.id;

            const price = parseFloat(v.sale_price ?? v.original_price ?? 0);
            const stock = parseInt(v.stock ?? 0, 10);

            if (priceBox) priceBox.textContent = new Intl.NumberFormat('vi-VN').format(price);
            if (stockBox) stockBox.textContent = stock;
            if (variantBox) variantBox.textContent = stock > 0 ? 'IN STOCK' : 'OUT OF STOCK';

            btn.disabled = stock <= 0;
          } else {
            selectedVariantId = null;
            btn.disabled = false;
            if(variantBox) variantBox.textContent = 'UNKNOWN';
          }
        }catch(e){
          selectedVariantId = null;
          btn.disabled = false;
          if(variantBox) variantBox.textContent = 'ERROR';
        }
      }

      if (hasSimpleVariants) {
        variantSelects.forEach(sel => sel.addEventListener('change', refreshSimpleVariant));
        refreshSimpleVariant();
      } else {
        colorSelect?.addEventListener('change', refreshVariant);
        sizeSelect?.addEventListener('change', refreshVariant);
      }

      btn.addEventListener('click', async () => {
        const url = btn.dataset.url;
        const qty = Math.max(1, parseInt(qtyInput?.value || '1', 10));

        btn.disabled = true;
        const old = btn.textContent;
        btn.textContent = 'Đang thêm...';

        try{
          const res = await fetch(url, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrf,
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(
              selectedVariantKey
                ? { qty, variant_key: selectedVariantKey }
                : (selectedVariantId ? { qty, variant_id: selectedVariantId } : { qty })
            )
          });

          const data = await res.json().catch(()=> ({}));
          if(!res.ok || !data.success) throw new Error(data.message || 'Add failed');

          msg.textContent = data.message || 'Đã thêm vào giỏ hàng';
          msg.style.color = 'rgba(69,255,154,.92)';
          
          if (typeof window.updateCartBadge === 'function') {
             window.updateCartBadge(data.cart_count);
          }
          window.dispatchEvent(new CustomEvent('cart:updated', { detail: { count: data.cart_count } }));

        }catch(e){
          msg.textContent = 'Có lỗi xảy ra. Vui lòng thử lại.';
          msg.style.color = 'rgba(248,113,113,.95)';

        }finally{
          btn.disabled = false;
          btn.textContent = old;
        }
      });
    });
  })();
