@props([
  'product',
  'variant' => 'home',
  'href' => null,
  'showBadges' => false,
  'showActions' => false,
])

@php
  $href = $href ?? route('clearance.show', ['slug' => $product->slug ?? $product->id]);
  $imageUrl = $product->image_url ?: ($variant === 'home' ? ('https://picsum.photos/400/400?random=' . $product->id) : asset('image/logo.png'));

  $titleLines = $variant === 'index' ? 3 : 2;

  $currentPrice = null;
  $oldPrice = null;

  if (isset($product->display_price)) {
    $currentPrice = (float) $product->display_price;
    $oldPrice = isset($product->display_original_price) ? (float) $product->display_original_price : null;
    if ($oldPrice && $currentPrice >= $oldPrice) {
      $oldPrice = null;
    }
  } else {
    $base = isset($product->original_price) ? (float) $product->original_price : (float) ($product->price ?? 0);
    $sale = isset($product->sale_price) ? (float) $product->sale_price : 0;
    if ($sale > 0 && $sale < $base) {
      $currentPrice = $sale;
      $oldPrice = $base;
    } else {
      $currentPrice = $base;
    }
  }
@endphp

<article {{ $attributes->merge(['class' => "pd-card pd-card--{$variant} pd-frame group"]) }}>
  <div class="pd-link">
    <a href="{{ $href }}" class="pd-media" aria-label="Xem chi tiết {{ $product->name }}">
      <img
        src="{{ $imageUrl }}"
        alt="{{ $product->name }}"
        loading="lazy"
        decoding="async"
        class="pd-img"
      >

      @if($showBadges)
        <div class="pd-badges">
          @if(($product->stock ?? 0) <= 0)
            <span class="pd-badge pd-badge--muted">Hết hàng</span>
          @else
            @if(($product->discount_percentage ?? 0) > 0)
              <span class="pd-badge">-{{ $product->discount_percentage }}%</span>
            @endif
            @if(isset($product->created_at) && $product->created_at->diffInDays(now()) < 14)
              <span class="pd-badge pd-badge--new">Mới</span>
            @endif
          @endif
        </div>
      @endif
    </a>

    <div class="pd-body">
      <a href="{{ $href }}" class="pd-title-link">
        <h3 class="pd-title pd-title--{{ $titleLines }}" title="{{ $product->name }}">
          {{ $product->name }}
        </h3>
      </a>

      <div class="pd-bottom">
        <div class="pd-price">
          <div class="pd-price__now">{{ number_format($currentPrice) }}đ</div>
          @if($oldPrice)
            <div class="pd-price__old">{{ number_format($oldPrice) }}đ</div>
          @endif
        </div>

        <div class="pd-actions">
          <a href="{{ $href }}" class="pd-cta">{{ $showActions ? 'Mua ngay' : 'Xem ngay' }}</a>

          @if($showActions)
            <button
              type="button"
              class="pd-cart btn-add-to-cart"
              data-url="{{ route('cart.add', $product->id) }}"
              aria-label="Thêm vào giỏ"
              title="Thêm vào giỏ"
            >
              <svg class="pd-cart__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </button>
          @endif
        </div>
      </div>
    </div>
  </div>
</article>
