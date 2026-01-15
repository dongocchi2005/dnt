@extends('frontend.layouts.app')

@section('title', 'Landing Sản phẩm | DNT Store')

@push('head')
  @vite(['resources/css/pages/products-landing.css', 'resources/js/pages/products-landing.js'])
@endpush

@section('content')
@php
  $fmt = fn ($v) => number_format((float) $v, 0, ',', '.') . ' ₫';
@endphp

<div class="pl-page" x-data="productsLanding(@js($payload))" x-init="init()">
  <section class="pl-hero" data-pl-reveal>
    <div class="pl-hero__bg" aria-hidden="true"></div>

    <div class="pl-hero__inner">
      <div class="pl-kicker">DNT STORE • PRODUCT LANDING</div>
      <h1 class="pl-hero__title">Sản phẩm công nghệ – Giá tốt – Bảo hành rõ</h1>
      <p class="pl-hero__sub">Chọn nhanh theo nhu cầu, xem ưu đãi, mua trong 30 giây.</p>

      <div class="pl-hero__cta">
        <a class="pl-btn pl-btn--primary" href="#pl-best">Xem bán chạy</a>
        <a class="pl-btn pl-btn--ghost" href="{{ $listingUrl }}">Xem tất cả</a>
      </div>

      <div class="pl-trust" role="list" aria-label="Cam kết">
        <div class="pl-trust__item" role="listitem">
          <div class="pl-trust__label">Đổi trả 7 ngày</div>
          <div class="pl-trust__sub">Nhanh gọn, minh bạch</div>
        </div>
        <div class="pl-trust__item" role="listitem">
          <div class="pl-trust__label">Bảo hành</div>
          <div class="pl-trust__sub">Rõ điều khoản, dễ tra cứu</div>
        </div>
        <div class="pl-trust__item" role="listitem">
          <div class="pl-trust__label">Giao nhanh</div>
          <div class="pl-trust__sub">Đóng gói chuẩn, theo dõi đơn</div>
        </div>
      </div>
    </div>
  </section>

  <section class="pl-section" data-pl-reveal>
    <div class="pl-head">
      <div>
        <h2 class="pl-h2">Chọn nhanh theo nhu cầu</h2>
        <p class="pl-p">Chạm một danh mục để lọc “Sản phẩm nổi bật”.</p>
      </div>
      <a class="pl-link" href="{{ $listingUrl }}">Xem tất cả sản phẩm</a>
    </div>

    <div class="pl-cats" role="list" aria-label="Danh mục">
      <a
        class="pl-cat"
        href="{{ route('products.landing') }}"
        @click.prevent="selectCategory('')"
        :class="selectedCategory === '' ? 'is-active' : ''"
        role="listitem"
      >
        <div class="pl-cat__thumb">
          <img src="{{ asset('image/logo.png') }}" alt="Tất cả" loading="lazy" decoding="async">
        </div>
        <div class="pl-cat__name">Tất cả</div>
      </a>

      @foreach($categories as $cat)
        <a
          class="pl-cat"
          href="{{ route('products.landing', ['cat' => $cat['slug']]) }}"
          @click.prevent="selectCategory('{{ $cat['slug'] }}')"
          :class="selectedCategory === '{{ $cat['slug'] }}' ? 'is-active' : ''"
          role="listitem"
        >
          <div class="pl-cat__thumb">
            <img src="{{ $cat['image'] }}" alt="{{ $cat['name'] }}" loading="lazy" decoding="async">
          </div>
          <div class="pl-cat__name">{{ $cat['name'] }}</div>
        </a>
      @endforeach
    </div>
  </section>

  <section class="pl-section" data-pl-reveal>
    <div class="pl-head">
      <div>
        <h2 class="pl-h2">Sản phẩm nổi bật</h2>
        <p class="pl-p">Top picks được chọn để dễ chốt – rõ giá, rõ deal.</p>
      </div>
      <div class="pl-head__right">
        <span class="pl-pill" x-show="selectedCategory" x-text="selectedCategoryLabel()"></span>
      </div>
    </div>

    <div class="pl-grid pl-grid--featured">
      @foreach($featured as $p)
        @php
          $detailUrl = $productDetailRouteName ? route($productDetailRouteName, $p['slug']) : '#';
          $isSale = ($p['original_price'] ?? 0) > 0 && ($p['original_price'] ?? 0) > ($p['sale_price'] ?? 0);
        @endphp
        <article
          class="pl-card"
          x-show="matchCategory('{{ $p['category_slug'] ?? '' }}')"
          x-transition.opacity.duration.180ms
          data-cy-tilt="1"
          data-tilt-max="6"
          data-tilt-lift="6"
        >
          <a class="pl-card__media" href="{{ $detailUrl }}" aria-label="Xem chi tiết {{ $p['name'] }}">
            <img src="{{ $p['image'] }}" alt="{{ $p['name'] }}" loading="lazy" decoding="async">
            <div class="pl-card__badges">
              @foreach(($p['badges'] ?? []) as $badge)
                <span class="pl-badge">{{ $badge }}</span>
              @endforeach
            </div>
          </a>

          <div class="pl-card__body">
            <div class="pl-card__name">
              <a href="{{ $detailUrl }}">{{ $p['name'] }}</a>
            </div>

            <div class="pl-card__price">
              <div class="pl-price__new">{{ $fmt($p['sale_price'] ?? 0) }}</div>
              @if($isSale)
                <div class="pl-price__old">{{ $fmt($p['original_price'] ?? 0) }}</div>
              @endif
            </div>

            <div class="pl-card__meta">
              <div class="pl-rating" aria-label="Đánh giá {{ $p['rating'] ?? 0 }}/5">
                <span class="pl-rating__stars" style="--fill: {{ min(100, max(0, (($p['rating'] ?? 0) / 5) * 100)) }}%"></span>
                <span class="pl-rating__text">{{ number_format((float) ($p['rating'] ?? 0), 1) }}</span>
              </div>
              <div class="pl-meta">Đã bán {{ (int) ($p['sold_count'] ?? 0) }}</div>
            </div>

            <div class="pl-card__cta">
              <a class="pl-btn2 pl-btn2--primary" href="{{ $detailUrl }}">Xem chi tiết</a>

              @if(!($payload['isFallback'] ?? false) && \Illuminate\Support\Facades\Route::has('cart.add'))
                @if(($p['has_variants'] ?? false))
                  <button type="button" class="pl-btn2 pl-btn2--ghost" @click="openQuickView({{ (int) $p['id'] }})">Chọn biến thể</button>
                @else
                  <form method="POST" action="{{ route('cart.add', $p['id']) }}">
                    @csrf
                    <button type="submit" class="pl-btn2 pl-btn2--ghost">Thêm vào giỏ</button>
                  </form>
                @endif
              @else
                <button type="button" class="pl-btn2 pl-btn2--ghost" @click="fakeAddToCart()">Thêm vào giỏ</button>
              @endif
            </div>
          </div>
        </article>
      @endforeach
    </div>
  </section>

  <section class="pl-section" data-pl-reveal>
    <div class="pl-deal">
      <div class="pl-deal__left">
        <div class="pl-deal__kicker">Giảm giá hôm nay</div>
        <div class="pl-deal__title">Chốt deal nhanh – UI đẹp, giá rõ</div>
        <div class="pl-deal__sub">Đếm ngược chỉ mang tính UI, không ràng buộc backend.</div>
      </div>
      <div class="pl-deal__right">
        <div class="pl-countdown" data-pl-countdown data-target="today">
          <div class="pl-time"><span data-dd>00</span><small>Ngày</small></div>
          <div class="pl-time"><span data-hh>00</span><small>Giờ</small></div>
          <div class="pl-time"><span data-mm>00</span><small>Phút</small></div>
          <div class="pl-time"><span data-ss>00</span><small>Giây</small></div>
        </div>
        <a class="pl-btn pl-btn--primary" href="{{ $listingUrl }}">Xem ưu đãi</a>
      </div>
    </div>
  </section>

  <section id="pl-best" class="pl-section" data-pl-reveal>
    <div class="pl-head">
      <div>
        <h2 class="pl-h2">Bán chạy</h2>
        <p class="pl-p">8–12 sản phẩm được khách chọn nhiều.</p>
      </div>
      <a class="pl-link" href="{{ $listingUrl }}">Tất cả sản phẩm</a>
    </div>

    <div class="pl-grid pl-grid--best">
      @foreach($bestSellers as $p)
        @php
          $detailUrl = $productDetailRouteName ? route($productDetailRouteName, $p['slug']) : '#';
          $isSale = ($p['original_price'] ?? 0) > 0 && ($p['original_price'] ?? 0) > ($p['sale_price'] ?? 0);
        @endphp
        <article class="pl-mini" data-cy-tilt="1" data-tilt-max="5" data-tilt-lift="4">
          <a class="pl-mini__media" href="{{ $detailUrl }}">
            <img src="{{ $p['image'] }}" alt="{{ $p['name'] }}" loading="lazy" decoding="async">
          </a>
          <div class="pl-mini__body">
            <div class="pl-mini__name">
              <a href="{{ $detailUrl }}">{{ $p['name'] }}</a>
            </div>
            <div class="pl-mini__price">
              <span class="pl-mini__new">{{ $fmt($p['sale_price'] ?? 0) }}</span>
              @if($isSale)
                <span class="pl-mini__old">{{ $fmt($p['original_price'] ?? 0) }}</span>
              @endif
            </div>
            <div class="pl-mini__meta">⭐ {{ number_format((float) ($p['rating'] ?? 0), 1) }} • Đã bán {{ (int) ($p['sold_count'] ?? 0) }}</div>
          </div>
        </article>
      @endforeach
    </div>
  </section>

  <section class="pl-section" data-pl-reveal>
    <div class="pl-head">
      <div>
        <h2 class="pl-h2">Product Finder</h2>
        <p class="pl-p">Wizard 3 bước giúp tăng conversion.</p>
      </div>
    </div>

    <div class="pl-finder">
      <div class="pl-steps">
        <button type="button" class="pl-step" :class="finder.step === 1 ? 'is-active' : ''" @click="finder.step = 1">1</button>
        <button type="button" class="pl-step" :class="finder.step === 2 ? 'is-active' : ''" @click="finder.step = 2">2</button>
        <button type="button" class="pl-step" :class="finder.step === 3 ? 'is-active' : ''" @click="finder.step = 3">3</button>
      </div>

      <div class="pl-finder__grid">
        <div class="pl-field">
          <div class="pl-label">Bạn cần gì?</div>
          <div class="pl-choice">
            <button type="button" class="pl-chip" :class="finder.need === 'tai-nghe' ? 'is-active' : ''" @click="finder.need='tai-nghe'">Tai nghe</button>
            <button type="button" class="pl-chip" :class="finder.need === 'loa' ? 'is-active' : ''" @click="finder.need='loa'">Loa</button>
            <button type="button" class="pl-chip" :class="finder.need === 'phu-kien' ? 'is-active' : ''" @click="finder.need='phu-kien'">Phụ kiện</button>
          </div>
        </div>

        <div class="pl-field">
          <div class="pl-label">Ngân sách?</div>
          <div class="pl-choice">
            <button type="button" class="pl-chip" :class="finder.budget === 'low' ? 'is-active' : ''" @click="finder.budget='low'">Dưới 500k</button>
            <button type="button" class="pl-chip" :class="finder.budget === 'mid' ? 'is-active' : ''" @click="finder.budget='mid'">500k–1.5tr</button>
            <button type="button" class="pl-chip" :class="finder.budget === 'high' ? 'is-active' : ''" @click="finder.budget='high'">Trên 1.5tr</button>
          </div>
        </div>

        <div class="pl-field">
          <div class="pl-label">Ưu tiên?</div>
          <div class="pl-choice">
            <button type="button" class="pl-chip" :class="finder.priority === 'pin' ? 'is-active' : ''" @click="finder.priority='pin'">Pin</button>
            <button type="button" class="pl-chip" :class="finder.priority === 'bao-hanh' ? 'is-active' : ''" @click="finder.priority='bao-hanh'">Bảo hành</button>
            <button type="button" class="pl-chip" :class="finder.priority === 'gia' ? 'is-active' : ''" @click="finder.priority='gia'">Giá</button>
          </div>
        </div>
      </div>

      <div class="pl-finder__actions">
        <button type="button" class="pl-btn pl-btn--primary" @click="runFinder()">Xem gợi ý</button>
        <button type="button" class="pl-btn pl-btn--ghost" @click="resetFinder()">Làm lại</button>
      </div>

      <div class="pl-finder__result" x-show="finder.results.length" x-transition.opacity.duration.180ms>
        <div class="pl-result__head">
          <div class="pl-result__title">Gợi ý cho bạn</div>
          <div class="pl-result__sub">Chọn nhanh 1 sản phẩm để xem chi tiết.</div>
        </div>

        <div class="pl-grid pl-grid--result">
          <template x-for="item in finder.results" :key="item.id">
            <article class="pl-mini pl-mini--result">
              <a class="pl-mini__media" :href="detailUrl(item)">
                <img :src="item.image" :alt="item.name" loading="lazy" decoding="async">
              </a>
              <div class="pl-mini__body">
                <div class="pl-mini__name"><a :href="detailUrl(item)" x-text="item.name"></a></div>
                <div class="pl-mini__price">
                  <span class="pl-mini__new" x-text="formatPrice(item.sale_price)"></span>
                  <span class="pl-mini__old" x-show="item.original_price > item.sale_price" x-text="formatPrice(item.original_price)"></span>
                </div>
                <div class="pl-mini__meta"><span x-text="'⭐ ' + (item.rating ?? 0)"></span></div>
              </div>
            </article>
          </template>
        </div>
      </div>
    </div>
  </section>

  <section class="pl-section" data-pl-reveal>
    <div class="pl-head">
      <div>
        <h2 class="pl-h2">Khách nói gì?</h2>
        <p class="pl-p">Social proof giúp quyết nhanh hơn.</p>
      </div>
      <div class="pl-head__right">
        <div class="pl-avg">
          <div class="pl-avg__num" x-text="avgRating().toFixed(1)"></div>
          <div class="pl-avg__txt">điểm trung bình</div>
        </div>
      </div>
    </div>

    <div class="pl-reviews" x-data="{ i: 0 }">
      <div class="pl-reviews__track">
        @foreach($reviews as $r)
          <article class="pl-review" :class="i === {{ $loop->index }} ? 'is-active' : 'is-hidden'">
            <div class="pl-review__top">
              <div class="pl-review__avatar">
                <img src="{{ $r['user']['avatar'] ?? asset('image/logo.png') }}" alt="{{ $r['user']['name'] ?? 'Khách hàng' }}" loading="lazy" decoding="async">
              </div>
              <div class="pl-review__who">
                <div class="pl-review__name">{{ $r['user']['name'] ?? 'Khách hàng' }}</div>
                <div class="pl-review__stars">⭐ {{ (int) ($r['rating'] ?? 5) }}/5</div>
              </div>
            </div>
            <div class="pl-review__title">{{ $r['title'] ?? '' }}</div>
            <div class="pl-review__content">{{ $r['content'] ?? '' }}</div>
          </article>
        @endforeach
      </div>

      <div class="pl-reviews__dots" aria-label="Chuyển review">
        @foreach($reviews as $r)
          <button type="button" class="pl-dot" :class="i === {{ $loop->index }} ? 'is-active' : ''" @click="i={{ $loop->index }}"></button>
        @endforeach
      </div>

      <div class="pl-reviews__nav">
        <button type="button" class="pl-btn pl-btn--ghost" @click="i = (i - 1 + {{ max(1, count($reviews)) }}) % {{ max(1, count($reviews)) }}">Trước</button>
        <button type="button" class="pl-btn pl-btn--primary" @click="i = (i + 1) % {{ max(1, count($reviews)) }}">Sau</button>
      </div>
    </div>
  </section>

  <section class="pl-section" data-pl-reveal>
    <div class="pl-head">
      <div>
        <h2 class="pl-h2">FAQ</h2>
        <p class="pl-p">Các câu hỏi thường gặp trước khi mua.</p>
      </div>
    </div>

    <div class="pl-faq">
      <details class="pl-q">
        <summary class="pl-q__sum">Chính sách đổi trả thế nào?</summary>
        <div class="pl-q__ans">Đổi trả trong 7 ngày với điều kiện sản phẩm còn nguyên vẹn. Chi tiết xem tại trang chính sách.</div>
      </details>
      <details class="pl-q">
        <summary class="pl-q__sum">Bảo hành ra sao?</summary>
        <div class="pl-q__ans">Bảo hành theo từng dòng sản phẩm, hỗ trợ kiểm tra nhanh và hướng dẫn rõ ràng.</div>
      </details>
      <details class="pl-q">
        <summary class="pl-q__sum">Mua hàng nhanh nhất bằng cách nào?</summary>
        <div class="pl-q__ans">Chọn sản phẩm → xem chi tiết → thêm vào giỏ → thanh toán. Bạn cũng có thể nhấn “Chat AI” để được gợi ý.</div>
      </details>
    </div>
  </section>

  <section class="pl-cta" data-pl-reveal>
    <div class="pl-cta__panel">
      <div class="pl-cta__left">
        <div class="pl-kicker">READY TO BUY</div>
        <div class="pl-cta__title">Mua ngay, hoặc nhờ AI tư vấn đúng nhu cầu</div>
        <div class="pl-cta__sub">Tối ưu conversion: ít bước, rõ thông tin, không mờ/đục nền.</div>
      </div>
      <div class="pl-cta__right">
        <a class="pl-btn pl-btn--primary" href="{{ $listingUrl }}">Mua ngay</a>
        <a class="pl-btn pl-btn--ghost" href="#chat">Chat AI tư vấn</a>
        <a class="pl-btn pl-btn--ghost" href="{{ route('booking.create') }}">Đặt lịch dịch vụ</a>
      </div>
    </div>
  </section>

  <div class="pl-support" x-cloak>
    <button type="button" class="pl-support__btn" @click="openChat()">Chat AI</button>
    <a class="pl-support__btn" href="{{ route('cart.index') }}">Giỏ hàng</a>
    <a class="pl-support__btn" href="tel:0900000000">Hotline</a>
  </div>

  <div class="pl-modal" x-cloak x-show="quickView.open" x-transition.opacity.duration.160ms @keydown.escape.window="closeQuickView()">
    <div class="pl-modal__backdrop" @click="closeQuickView()"></div>
    <div class="pl-modal__panel" role="dialog" aria-modal="true">
      <div class="pl-modal__head">
        <div class="pl-modal__title" x-text="quickView.product?.name || 'Chọn biến thể'"></div>
        <button type="button" class="pl-modal__close" @click="closeQuickView()">✕</button>
      </div>
      <div class="pl-modal__body">
        <div class="pl-modal__hint">Quick-view chỉ UI. Chọn biến thể sẽ chuyển sang trang chi tiết để mua.</div>
        <div class="pl-modal__actions">
          <a class="pl-btn pl-btn--primary" :href="quickView.product ? detailUrl(quickView.product) : '#'" @click="closeQuickView()">Xem chi tiết</a>
          <button type="button" class="pl-btn pl-btn--ghost" @click="closeQuickView()">Để sau</button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

