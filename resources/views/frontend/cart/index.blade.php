@extends('frontend.layouts.app')

@section('title', 'Giỏ hàng | DNT Store')

@section('content')
<div class="cart-wrap">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-3xl font-extrabold neon">Giỏ hàng</h1>
    <a href="{{ route('home') }}" class="btn-cyber btn-outline">← Tiếp tục mua</a>
  </div>

  @if(empty($items))
    <div class="cart-card cy-scanlines cy-noise p-10 text-center">
      <div class="text-5xl mb-4">🛒</div>
      <div class="text-xl font-bold text-white cart-empty-title">Giỏ hàng đang trống</div>
      <div class="text-white/60 mt-2 cart-empty-sub">Thêm sản phẩm để tiếp tục.</div>
      <div class="mt-6">
        <a href="{{route('home')}}" class="btn-cyber">Đi mua ngay</a>
      </div>
    </div>
  @else
    <div class="cart-card cy-scanlines cy-noise">
      <div class="cart-head p-4 text-white/80 font-semibold flex items-center justify-between gap-4">
        <span>Sản phẩm trong giỏ</span>
        <label class="flex items-center gap-2 text-white/70 text-sm font-normal">
          <input type="checkbox" class="js-cart-select-all" checked>
          <span>Chọn tất cả</span>
        </label>
      </div>

      <div class="cart-list">
        @foreach($items as $key => $it)
          @php $lineTotal = ($it['price'] ?? 0) * ($it['qty'] ?? 0); @endphp
          <div class="cart-row cart-item-card js-cart-row" data-key="{{ $key }}" data-line-total="{{ $lineTotal }}">
            <div class="cart-check">
              <input type="checkbox" class="js-cart-item-check" value="{{ $key }}" checked aria-label="Chọn sản phẩm">
            </div>
            <div class="cart-thumb">
              <img src="{{ asset($it['image'] ?? 'image/bg.jpg') }}" alt="">
            </div>

            <div>
              <div class="cart-title">{{ $it['name'] ?? 'Sản phẩm' }}</div>
              <div class="cart-sub">
                Mã: #{{ $it['id'] }}
                @if(!empty($it['variant_name']))
                  • {{ $it['variant_name'] }}
                @endif
                {{-- Show options explicitly if not in variant_name --}}
                @if(!empty($it['options']))
                    <div class="text-xs text-white/50">
                    @foreach($it['options'] as $optName => $optVal)
                        <span class="inline-block mr-2 bg-white/10 px-1 rounded">{{ ucfirst($optName) }}: {{ $optVal }}</span>
                    @endforeach
                    </div>
                @endif
              </div>
            </div>

            <div class="cart-qty cart-actions">
              <form action="{{ route('cart.update', $key) }}" method="POST" class="js-cart-qty-form" data-key="{{ $key }}" data-price="{{ $it['price'] ?? 0 }}">
                @csrf
                <div class="qty-control">
                  <button class="qty-btn js-qty-btn" type="button" data-delta="-1" aria-label="Giảm số lượng">−</button>
                  <input type="number" name="qty" min="1" value="{{ $it['qty'] ?? 1 }}" class="js-qty-input" inputmode="numeric">
                  <button class="qty-btn js-qty-btn" type="button" data-delta="1" aria-label="Tăng số lượng">+</button>
                </div>
                {{-- Hidden input for key if needed --}}
                <input type="hidden" name="key" value="{{ $key }}">
              </form>
            </div>

            <div class="cart-price cart-actions">
              <span class="js-line-total">{{ number_format($lineTotal, 0, ',', '.') }} ₫</span>
            </div>

            <div class="cart-actions">
                <button class="cart-remove btn-remove-cart" type="button" data-url="{{ route('cart.remove', $key) }}" title="Xoá">✖</button>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <div class="sum-box">
      <div class="sum-panel cyber-panel cy-scanlines cy-noise">
        <div class="flex items-center justify-between text-white/80">
          <span>Tạm tính (đã chọn)</span>
          <strong class="text-cyan-300" id="js-selected-subtotal">{{ number_format($subtotal, 0, ',', '.') }} ₫</strong>
        </div>
        <div class="text-white/50 text-sm mt-2">
          Đã chọn <strong class="text-white/80" id="js-selected-count">0</strong> sản phẩm
        </div>

        <div class="text-white/50 text-sm mt-2">Phí vận chuyển/thi công sẽ tính ở bước thanh toán.</div>

        <div class="flex gap-3 mt-5">
          {{-- <form action="{{ route('cart.removeSelected') }}" method="POST" class="js-cart-batch-form" data-keys-name="keys[]">
            @csrf
            <button class="btn-cyber btn-outline js-remove-selected-btn" type="submit">Xoá đã chọn</button>
          </form> --}}

          {{-- <form action="{{ route('cart.clear') }}" method="POST">
            @csrf
            <button class="btn-cyber btn-outline" type="submit">Xoá giỏ</button>
          </form> --}}

          <form action="{{ route('payment.payOrder') }}" method="POST" class="js-cart-batch-form" data-keys-name="selected_keys[]">
            @csrf
            <div class="w-full mb-4">
              <label class="block text-white/80 font-semibold mb-2">Phương thức thanh toán</label>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input type="radio" name="payment_method" value="vietqr" checked class="mr-2">
                  <span class="text-white/80">Thanh toán online (VietQR)</span>
                </label>
                <label class="flex items-center">
                  <input type="radio" name="payment_method" value="cash_on_delivery" class="mr-2">
                  <span class="text-white/80">Thanh toán khi nhận hàng</span>
                </label>
              </div>
            </div>
            <button type="submit" class="btn-cyber js-checkout-btn">Thanh toán</button>
          </form>
        </div>
      </div>
    </div>
  @endif
</div>
@endsection

@push('scripts')
<script>
  (function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const selectAll = document.querySelector('.js-cart-select-all');
    const rows = Array.from(document.querySelectorAll('.js-cart-row'));
    const subtotalEl = document.getElementById('js-selected-subtotal');
    const selectedCountEl = document.getElementById('js-selected-count');

    const formatVnd = (n) => {
      try {
        return new Intl.NumberFormat('vi-VN').format(n) + ' ₫';
      } catch (e) {
        return String(n) + ' ₫';
      }
    };

    const getRowByKey = (key) => rows.find(r => r.dataset.key === key);

    const getSelectedKeys = () => {
      return Array.from(document.querySelectorAll('.js-cart-item-check:checked')).map(cb => cb.value);
    };

    const setHiddenKeys = (form, keys) => {
      const inputName = form.dataset.keysName;
      if (!inputName) return;
      form.querySelectorAll('input[type="hidden"][data-cart-key="1"]').forEach(el => el.remove());
      keys.forEach((k) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = inputName;
        input.value = k;
        input.dataset.cartKey = '1';
        form.appendChild(input);
      });
    };

    const updateSelectedUI = () => {
      const checked = Array.from(document.querySelectorAll('.js-cart-item-check'));
      const checkedOn = checked.filter(cb => cb.checked);
      if (selectAll) {
        selectAll.checked = checkedOn.length > 0 && checkedOn.length === checked.length;
        selectAll.indeterminate = checkedOn.length > 0 && checkedOn.length < checked.length;
      }

      let sum = 0;
      checkedOn.forEach(cb => {
        const row = getRowByKey(cb.value);
        sum += Number(row?.dataset.lineTotal || 0);
      });

      if (subtotalEl) subtotalEl.textContent = formatVnd(sum);
      if (selectedCountEl) selectedCountEl.textContent = String(checkedOn.length);

      const disable = checkedOn.length === 0;
      document.querySelectorAll('.js-remove-selected-btn, .js-checkout-btn').forEach(btn => {
        btn.toggleAttribute('disabled', disable);
        btn.classList.toggle('opacity-50', disable);
        btn.classList.toggle('cursor-not-allowed', disable);
      });

      document.querySelectorAll('form.js-cart-batch-form').forEach(form => setHiddenKeys(form, checkedOn.map(cb => cb.value)));
    };

    document.addEventListener('change', (e) => {
      const cb = e.target.closest('.js-cart-item-check');
      if (cb) updateSelectedUI();
      if (e.target === selectAll) {
        const to = !!selectAll.checked;
        document.querySelectorAll('.js-cart-item-check').forEach(c => (c.checked = to));
        updateSelectedUI();
      }
    });

    const postQty = async (form) => {
      const fd = new FormData(form);
      const res = await fetch(form.action, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json'
        },
        body: fd
      });
      if (!res.ok) throw new Error('update_failed');
      return await res.json();
    };

    const syncRowLineTotal = (key, lineTotal) => {
      const row = getRowByKey(key);
      if (!row) return;
      row.dataset.lineTotal = String(lineTotal || 0);
      const el = row.querySelector('.js-line-total');
      if (el) el.textContent = formatVnd(Number(lineTotal || 0));
    };

    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('.js-qty-btn');
      if (!btn) return;
      const form = btn.closest('form.js-cart-qty-form');
      if (!form) return;

      const input = form.querySelector('input.js-qty-input');
      if (!input) return;

      const delta = Number(btn.dataset.delta || 0);
      const current = Number(input.value || 1);
      const next = Math.max(1, current + delta);
      input.value = String(next);

      try {
        const data = await postQty(form);
        syncRowLineTotal(form.dataset.key, data.item_line_total);
        if (typeof window.updateCartBadge === 'function' && data.cart_count !== undefined) {
            window.updateCartBadge(data.cart_count);
        }
        updateSelectedUI();
      } catch (err) {
        form.submit();
      }
    });

    document.addEventListener('change', async (e) => {
      const input = e.target.closest('input.js-qty-input');
      if (!input) return;
      const form = input.closest('form.js-cart-qty-form');
      if (!form) return;
      const v = Math.max(1, Number(input.value || 1));
      input.value = String(v);

      try {
        const data = await postQty(form);
        syncRowLineTotal(form.dataset.key, data.item_line_total);
        if (typeof window.updateCartBadge === 'function' && data.cart_count !== undefined) {
            window.updateCartBadge(data.cart_count);
        }
        updateSelectedUI();
      } catch (err) {
        form.submit();
      }
    });

    document.addEventListener('submit', (e) => {
      const form = e.target;
      if (!(form instanceof HTMLFormElement)) return;
      if (!form.classList.contains('js-cart-batch-form')) return;
      const keys = getSelectedKeys();
      if (keys.length === 0) {
        e.preventDefault();
        e.stopImmediatePropagation();
        return;
      }
      setHiddenKeys(form, keys);
    });

    updateSelectedUI();
  })();
</script>
@endpush
