@extends('frontend.layouts.app')

@section('title', $product->name)

@section('styles')
  @vite(['resources/css/pages/clearance-show.css'])
@endsection

@php
  $imgUrl = function ($path) {
      if (!$path) return null;
      if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) return $path;
      if (\Illuminate\Support\Str::startsWith($path, ['image/', '/image', 'images/', '/images'])) return asset($path);
      if (\Illuminate\Support\Str::startsWith($path, ['storage/', '/storage'])) return asset($path);
      return \Illuminate\Support\Facades\Storage::url($path);
  };

  $baseMain = $imgUrl($product->image ?? null) ?: asset('images/no-image.jpg');
  $imagesIn = $product->images ?? [];
  $galleryCandidates = collect([]);
  if ($imagesIn instanceof \Illuminate\Support\Collection) {
      $galleryCandidates = $imagesIn->map(function ($item) use ($imgUrl) {
          if (is_string($item)) return $imgUrl($item);
          if (is_array($item)) return $imgUrl($item['image'] ?? $item['url'] ?? null);
          if (is_object($item)) return $imgUrl($item->image ?? $item->url ?? null);
          return null;
      });
  } elseif (is_array($imagesIn)) {
      $galleryCandidates = collect($imagesIn)->map(function ($item) use ($imgUrl) {
          if (is_string($item)) return $imgUrl($item);
          if (is_array($item)) return $imgUrl($item['image'] ?? $item['url'] ?? null);
          if (is_object($item)) return $imgUrl($item->image ?? $item->url ?? null);
          return null;
      });
  }
  $gallery = collect([$baseMain])->merge($galleryCandidates->filter())->unique()->values();

  $price = $initialVariant?->effective_price ?? ($product->display_price ?? $product->price ?? null);
  $oldPrice = $initialVariant?->price ?? ($product->display_original_price ?? null);
  $discountPercent = ($oldPrice && $price && (float)$oldPrice > 0)
      ? max(0, round((($oldPrice - $price) / $oldPrice) * 100))
      : 0;

  $stock = (int)($initialVariant?->stock ?? ($product->stock ?? 0));
  $sku = $initialVariant?->sku ?? null;
  $saleEndsAt = $product->sale_ends_at ?? null;
@endphp

@section('content')
<section class="cl-wrap" data-product-id="{{ $product->id }}"
         data-cart-index="{{ route('cart.index') }}"
         data-end="{{ $saleEndsAt ? \Illuminate\Support\Carbon::parse($saleEndsAt)->toIso8601String() : '' }}">
  <div class="cl-container">
    <header class="cl-heading">
      <div>
        <h1 class="cl-h1">
          <span class="neon">{{ $product->name }}</span>
        </h1>
        <div class="cl-sub">
          Hàng xả kho • Công nghệ • Cyber • Neon
        </div>
      </div>
      <div class="badge">
        <span class="b-left">CLEARANCE</span>
        <span class="b-right">{{ $discountPercent > 0 ? ('-' . $discountPercent . '%') : 'FLASH' }}</span>
      </div>
    </header>

    <div class="cl-grid">
      <div class="stage">
        <div class="hero" id="js-hero">
          <img id="js-hero-img" class="hero-img" src="{{ $gallery->first() ?: asset('images/no-image.jpg') }}" alt="{{ $product->name }}">
          <div class="hero-nav">
            <button type="button" class="nav-btn" id="js-prev" aria-label="Ảnh trước">&#x2039;</button>
            <button type="button" class="nav-btn" id="js-next" aria-label="Ảnh sau">&#x203A;</button>
          </div>
        </div>

        <div class="thumbs" id="js-thumbs" aria-label="Gallery thumbnails">
          @foreach($gallery as $g)
            <button type="button" class="thumb{{ $loop->first ? ' active' : '' }}" data-src="{{ $g }}">
              <img src="{{ $g }}" alt="thumb {{ $loop->index + 1 }}">
            </button>
          @endforeach
        </div>
      </div>

      <aside class="buy">
        <div class="pricebox">
          <div class="price" data-vp-price>
            @if($price !== null)
              {{ number_format((float)$price, 0, ',', '.') }} ₫
            @else
              Liên hệ
            @endif
          </div>
          @if($oldPrice && $price && (float)$oldPrice > (float)$price)
            <div class="old">{{ number_format((float)$oldPrice, 0, ',', '.') }} ₫</div>
          @endif
          <div class="note">Giá sốc • Cyber Neon</div>
        </div>

        <div class="stock">
          <span class="label">Tồn kho:</span>
          <span class="qty {{ $stock > 0 ? 'ok' : 'zero' }}" data-vp-stock data-vp-stock-mode="qty">{{ (int)$stock }}</span>
        </div>

        <div class="selectors">
          @include('frontend.products._variant-picker', [
              'product' => $product,
              'variantOptions' => $variantOptions ?? [],
              'variantsPayload' => $variantsPayload ?? [],
              'initialVariant' => $initialVariant ?? null,
          ])
          <div class="selector">
            <div class="label">Số lượng</div>
            <div class="qtybox">
              <button type="button" class="qty-btn" id="js-dec" aria-label="Giảm">−</button>
              <input type="number" id="js-qty" class="qty-input" min="1" value="1" inputmode="numeric">
              <button type="button" class="qty-btn" id="js-inc" aria-label="Tăng">+</button>
            </div>
          </div>
        </div>

        <div class="cta">
          <button type="button"
                  class="cy-btn primary btn-buy-now"
                  data-checkout-url="{{ route('checkout.buyNow', $product->id) }}"
                  data-vp-cta
                  {{ $stock <= 0 ? 'disabled' : '' }}>
            MUA NGAY
          </button>
          <button type="button"
                  class="cy-btn ghost btn-add-to-cart"
                  data-url="{{ route('cart.add', $product->id) }}"
                  data-vp-cta
                  {{ $stock <= 0 ? 'disabled' : '' }}>
            THÊM GIỎ
          </button>
        </div>

        <div class="countdown is-hidden" id="js-countdown">
          Kết thúc sau: <span id="js-count-val">--:--:--</span>
        </div>

        <div class="info">
          <div class="info-item">
            <i class="fa-solid fa-barcode"></i>
            <span class="h">SKU</span>
            <span class="spec" data-vp-sku>{{ $sku ?? 'N/A' }}</span>
          </div>
          <div class="info-item">
            <i class="fa-solid fa-shield-halved"></i>
            <span class="h">Tình trạng</span>
            <span class="spec">Hàng mới • Bảo hành chính hãng</span>
          </div>
          <div class="info-item">
            <i class="fa-solid fa-truck-fast"></i>
            <span class="h">Chính sách nhanh</span>
            <span class="spec">Giao hỏa tốc • Đổi trả 7 ngày</span>
          </div>
        </div>
      </aside>
    </div>

    <div class="details">
      <div class="details-head">
        <div class="details-title">Mô tả sản phẩm</div>
        <div class="details-sub">Thông tin kỹ thuật • Trải nghiệm • Lưu ý</div>
      </div>
      <div class="details-body">
        {!! $product->description ?? '<p>Chưa có mô tả.</p>' !!}
      </div>
    </div>
  </div>

  <div class="sticky-cta" id="js-sticky-cta">
    <div class="sticky-inner">
      <div class="sticky-price" data-vp-price>
        @if($price !== null)
          {{ number_format((float)$price, 0, ',', '.') }} ₫
        @else
          Liên hệ
        @endif
      </div>
      <div class="sticky-actions">
        <button type="button"
                class="cy-btn primary btn-buy-now"
                data-checkout-url="{{ route('checkout.buyNow', $product->id) }}"
                data-vp-cta
                {{ $stock <= 0 ? 'disabled' : '' }}>
          MUA NGAY
        </button>
        <button type="button"
                class="cy-btn ghost btn-add-to-cart"
                data-url="{{ route('cart.add', $product->id) }}"
                data-vp-cta
                {{ $stock <= 0 ? 'disabled' : '' }}>
          THÊM GIỎ
        </button>
      </div>
    </div>
  </div>
</section>
@endsection

@section('scripts')
  @vite(['resources/js/pages/clearance-show.js'])
  <script>
    (function () {
      function showToast(message) {
        try {
          var toast = document.getElementById('cyber-toast');
          if (!toast) return;
          var span = toast.querySelector('span');
          if (span) span.textContent = message;
          toast.classList.add('show');
          setTimeout(function () { toast.classList.remove('show'); }, 2200);
        } catch (e) {}
      }

      document.addEventListener('click', function (e) {
        var btn = e.target && e.target.closest && e.target.closest('body[data-page="clearance.show"] .btn-buy-now');
        if (!btn) return;

        var checkoutUrl = btn.getAttribute('data-checkout-url');
        if (!checkoutUrl) return;

        e.preventDefault();
        e.stopImmediatePropagation();

        var qtyInput = document.getElementById('js-qty');
        var qty = Number(qtyInput && qtyInput.value ? qtyInput.value : 1);
        if (!Number.isFinite(qty) || qty < 1) qty = 1;

        var variantIdEl = document.querySelector('[data-variant-picker] .vp-variant-id');
        var variantId = variantIdEl ? variantIdEl.value : '';
        if (!variantId) {
          showToast('Vui lòng chọn biến thể');
          return;
        }

        var u = new URL(checkoutUrl, window.location.origin);
        u.searchParams.set('qty', String(qty));
        u.searchParams.set('variant_id', String(variantId));
        window.location.assign(u.toString());
      }, true);
    })();
  </script>
@endsection
