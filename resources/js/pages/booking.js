// resources/js/pages/booking.js
(() => {
  document.addEventListener('DOMContentLoaded', () => {
    // CHỐT: chỉ chạy khi đúng booking page + đúng form
    const page = document.querySelector('.booking-page[data-page="booking"]');
    const form = document.getElementById('booking-form');
    if (!page || !form) return;

    // ==== Helpers ====
    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

    const getReceiveMethod = () => {
      const checked = $('input[name="receive_method"]:checked', form);
      return checked ? checked.value : 'store';
    };

    const setMinAppointment = () => {
      const appointmentInput = $('#appointment_at', form);
      if (!appointmentInput) return;

      // min = now + 30 phút (chuẩn datetime-local)
      const now = new Date();
      now.setMinutes(now.getMinutes() + 30);
      now.setSeconds(0, 0);

      // datetime-local cần local time, tránh lệch timezone
      const local = new Date(now.getTime() - now.getTimezoneOffset() * 60000);
      appointmentInput.min = local.toISOString().slice(0, 16);
    };

    const toggleShipBlocks = () => {
      const method = getReceiveMethod(); // store | pickup | shipping
      const blocks = $$('.booking-ship-only', form);
      if (!blocks.length) return;

      blocks.forEach((el) => {
        const mode = el.getAttribute('data-ship'); // pickup | shipping
        const show = (method === mode);

        el.style.display = show ? '' : 'none';

        // Disable input bên trong khi ẩn để tránh bị validate/submit nhầm
        $$('input, select, textarea', el).forEach((inp) => {
          inp.disabled = !show;
        });
      });
    };

    const formatDateTimeVi = (value) => {
      if (!value) return '—';
      const d = new Date(value);
      if (Number.isNaN(d.getTime())) return '—';

      return d.toLocaleString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      });
    };

    const setSummary = (key, value) => {
      const el = document.querySelector(`[data-summary="${key}"]`);
      if (el) el.textContent = value ?? '—';
    };

    const toggleSummaryShipRows = () => {
      const method = getReceiveMethod();
      const wraps = $$('[data-summary-wrap]');
      if (!wraps.length) return;

      wraps.forEach((row) => {
        const k = row.getAttribute('data-summary-wrap'); // shipping_provider | pickup_address | shipping_code
        const show =
          (method === 'shipping' && (k === 'shipping_provider' || k === 'shipping_code')) ||
          (method === 'pickup' && k === 'pickup_address');

        row.style.display = show ? '' : 'none';
      });
    };

    const updateSummary = () => {
      const name = $('#name', form)?.value?.trim() || '—';
      const phone = $('#phone', form)?.value?.trim() || '—';
      const device = $('#device', form)?.value?.trim() || '—';
      const appointment = $('#appointment_at', form)?.value || '';
      const method = getReceiveMethod();

      setSummary('name', name);
      setSummary('phone', phone);
      setSummary('device', device);
      setSummary('appointment_at', formatDateTimeVi(appointment));
      setSummary(
        'receive_method',
        method === 'store' ? 'Mang tới cửa hàng'
          : method === 'pickup' ? 'Nhận tận nơi'
          : 'Gửi ship'
      );

      // ship extra
      setSummary('shipping_provider', $('#shipping_provider', form)?.value?.trim() || '—');
      setSummary('pickup_address', $('#pickup_address', form)?.value?.trim() || '—');
      setSummary('shipping_code', $('#shipping_code', form)?.value?.trim() || '—');

      toggleSummaryShipRows();
    };

    // ==== Booking FAQ Accordion (đúng markup của bạn) ====
    const initAccordion = () => {
      const acc = document.querySelector('[data-accordion]');
      if (!acc) return;

      const btns = $$('[data-acc-btn]', acc);

      btns.forEach((btn) => {
        btn.addEventListener('click', () => {
          const qa = btn.closest('.booking-qa');
          if (!qa) return;

          const isOpen = qa.classList.contains('is-open');

          // close all
          $$('.booking-qa', acc).forEach((x) => x.classList.remove('is-open'));

          // toggle current
          if (!isOpen) qa.classList.add('is-open');
        });
      });
    };

    // ==== Bind events (an toàn, không throw) ====
    setMinAppointment();
    toggleShipBlocks();
    updateSummary();
    initAccordion();

    // Receive method change
    $$('input[name="receive_method"]', form).forEach((r) => {
      r.addEventListener('change', () => {
        toggleShipBlocks();
        updateSummary();
      });
    });

    // Live update summary on inputs
    ['name', 'phone', 'device', 'appointment_at', 'shipping_provider', 'pickup_address', 'shipping_code'].forEach((id) => {
      const el = document.getElementById(id);
      if (!el) return;
      el.addEventListener('input', updateSummary);
      el.addEventListener('change', updateSummary);
    });

    // QUAN TRỌNG: Không intercept submit (để Laravel redirect/session hoạt động đúng)
    // Nếu bạn muốn Ajax thì làm riêng; bản này FIX ổn định trước.
  });
})();
