@extends('frontend.layouts.app')

@section('title', $product->name ?? 'Product Detail')

@section('styles')
<style>
  :root {
    --m56-bg: #000000;
    --m56-fg: #e8e8e8;
    --m56-muted: #9aa0a6;
    --m56-accent: #ffcc00;
    --m56-accent2: rgba(255, 204, 0, 0.35);
    --m56-panel: rgba(10, 10, 10, 0.85);
  }
  .m56-page { background: var(--m56-bg); color: var(--m56-fg); }
  .m56-cut { clip-path: polygon(16px 0, calc(100% - 16px) 0, 100% 16px, 100% calc(100% - 16px), calc(100% - 16px) 100%, 16px 100%, 0 calc(100% - 16px), 0 16px); }
  .m56-cut-tight { clip-path: polygon(12px 0, calc(100% - 12px) 0, 100% 12px, 100% calc(100% - 12px), calc(100% - 12px) 100%, 12px 100%, 0 calc(100% - 12px), 0 12px); }
  .m56-cut-cta { clip-path: polygon(18px 0, calc(100% - 18px) 0, 100% 18px, 100% 100%, 0 100%, 0 18px); }
  .m56-gridline {
    background-image:
      linear-gradient(transparent 0 31px, rgba(255, 255, 255, 0.05) 32px),
      linear-gradient(90deg, transparent 0 31px, rgba(255, 255, 255, 0.05) 32px);
    background-size: 32px 32px;
  }
  .m56-glow { box-shadow: 0 0 0 1px rgba(255, 204, 0, 0.45), 0 0 18px rgba(255, 204, 0, 0.25); }
  .m56-glow-strong { box-shadow: 0 0 0 1px rgba(255, 204, 0, 0.75), 0 0 28px rgba(255, 204, 0, 0.4); }
  .m56-cta {
    background: linear-gradient(90deg, rgba(255, 204, 0, 0.95), rgba(255, 204, 0, 0.7));
    color: #000;
    text-transform: uppercase;
    letter-spacing: 0.14em;
  }
  .m56-cta:hover { filter: brightness(1.05); }
  .m56-outline { border: 1px solid rgba(255, 204, 0, 0.35); }
  .m56-outline-weak { border: 1px solid rgba(255, 255, 255, 0.1); }
  .m56-vert {
    position: fixed;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    writing-mode: vertical-rl;
    text-orientation: mixed;
    letter-spacing: 0.22em;
    color: rgba(255, 255, 255, 0.55);
    font-family: "DM Sans", ui-sans-serif, system-ui;
    font-size: 12px;
    z-index: 40;
    pointer-events: none;
    user-select: none;
  }
  .m56-glitch {
    position: relative;
    display: inline-block;
  }
  .m56-glitch::before,
  .m56-glitch::after {
    content: attr(data-text);
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    overflow: hidden;
    mix-blend-mode: screen;
    opacity: 0.55;
  }
  .m56-glitch::before { transform: translate(1px, 0); color: rgba(255, 204, 0, 0.75); clip-path: inset(0 0 55% 0); animation: m56Gl1 2.3s infinite linear; }
  .m56-glitch::after { transform: translate(-1px, 0); color: rgba(120, 120, 120, 0.75); clip-path: inset(45% 0 0 0); animation: m56Gl2 1.9s infinite linear; }
  @keyframes m56Gl1 {
    0% { clip-path: inset(0 0 55% 0); }
    30% { clip-path: inset(8% 0 42% 0); }
    60% { clip-path: inset(2% 0 60% 0); }
    100% { clip-path: inset(0 0 55% 0); }
  }
  @keyframes m56Gl2 {
    0% { clip-path: inset(45% 0 0 0); }
    25% { clip-path: inset(52% 0 0 0); }
    55% { clip-path: inset(38% 0 0 0); }
    100% { clip-path: inset(45% 0 0 0); }
  }
</style>
@endsection

@section('content')
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
  } else {
      $galleryCandidates = collect([]);
  }

  $gallery = collect([$baseMain])
      ->merge($galleryCandidates->filter())
      ->unique()
      ->values();

  $sizesIn = $product->sizes ?? null;
  $stylesIn = $product->styles ?? null;

  $sizes = collect([]);
  $styles = collect([]);

  if ($sizesIn instanceof \Illuminate\Support\Collection) $sizes = $sizesIn;
  elseif (is_array($sizesIn)) $sizes = collect($sizesIn);

  if ($stylesIn instanceof \Illuminate\Support\Collection) $styles = $stylesIn;
  elseif (is_array($stylesIn)) $styles = collect($stylesIn);

  if ($sizes->count() === 0 && isset($product->variants) && $product->variants instanceof \Illuminate\Support\Collection) {
      $sizes = $product->variants->pluck('size')->filter()->unique()->values();
  }
  if ($styles->count() === 0 && isset($product->variants) && $product->variants instanceof \Illuminate\Support\Collection) {
      $styles = $product->variants->pluck('color')->filter()->unique()->values();
  }

  $price = $product->price ?? null;
  $oldPrice = $product->old_price ?? null;
  $salePrice = $product->sale_price ?? null;
  if ($salePrice && (!$price || $salePrice < $price)) {
      $oldPrice = $price;
      $price = $salePrice;
  }
@endphp

<div class="m56-vert hidden lg:block">M56-50/60 // IN VECTOR WE TRUST</div>

<div
  class="m56-page m56-gridline m56-cut px-4 py-8 md:px-8 md:py-10"
  x-data="m56ProductDetail({
    productId: @json($product->id ?? null),
    images: @json($gallery->values()),
    sizes: @json($sizes->values()),
    styles: @json($styles->values()),
    price: @json($price),
    oldPrice: @json($oldPrice),
  })"
>
  <div class="max-w-7xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      <div class="lg:col-span-7">
        <div class="relative m56-cut m56-outline-weak bg-black/70 overflow-hidden">
          <div class="absolute inset-0 pointer-events-none opacity-50" style="background: radial-gradient(circle at 20% 10%, rgba(255,204,0,0.12), transparent 45%), radial-gradient(circle at 70% 80%, rgba(255,204,0,0.08), transparent 48%);"></div>

          <div class="p-4 md:p-6">
            <div class="relative aspect-[4/3] md:aspect-[16/11] flex items-center justify-center">
              <img
                class="max-h-full max-w-full object-contain transition-opacity duration-200 select-none"
                :src="activeImage"
                :alt="productName"
                x-on:load="imageLoaded = true"
                x-on:error="fallbackImage($event)"
                x-show="activeImage"
              >

              <div class="absolute left-4 top-4 bg-white text-black px-4 py-3 font-sans text-xs md:text-sm tracking-widest uppercase m56-cut-tight shadow-[0_18px_40px_rgba(0,0,0,0.35)]">
                HEAVY DUTY<br>RUGGED
              </div>

              <div class="absolute top-0 left-0 w-6 h-6 border-t border-l border-yellow-400/60"></div>
              <div class="absolute top-0 right-0 w-6 h-6 border-t border-r border-yellow-400/60"></div>
              <div class="absolute bottom-0 left-0 w-6 h-6 border-b border-l border-yellow-400/60"></div>
              <div class="absolute bottom-0 right-0 w-6 h-6 border-b border-r border-yellow-400/60"></div>
            </div>
          </div>
        </div>

        <div class="mt-4">
          <div class="grid grid-cols-5 sm:grid-cols-6 md:grid-cols-8 gap-2">
            <template x-for="(img, idx) in images" :key="img + idx">
              <button
                type="button"
                class="relative m56-cut-tight bg-black/80 m56-outline-weak overflow-hidden aspect-square"
                :class="isActiveThumb(img) ? 'm56-glow' : ''"
                @click="setActive(img)"
              >
                <img class="w-full h-full object-cover opacity-90 hover:opacity-100 transition" :src="img" alt="">
                <div class="absolute inset-0 pointer-events-none" :class="isActiveThumb(img) ? 'ring-1 ring-yellow-400/80' : 'ring-1 ring-white/5'"></div>
              </button>
            </template>
          </div>
        </div>
      </div>

      <div class="lg:col-span-5">
        <div class="m56-cut bg-[rgba(10,10,10,0.85)] m56-outline-weak p-6 md:p-8">
          <div class="flex items-start justify-between gap-4">
            <div>
              <h1 class="font-sans text-2xl md:text-3xl tracking-wide leading-tight">
                <span class="m56-glitch" data-text="{{ $product->name ?? 'M56 MACHINE56' }}">{{ $product->name ?? 'M56 MACHINE56' }}</span>
              </h1>
              <div class="mt-2 text-xs md:text-sm text-white/60 font-sans tracking-[0.22em] uppercase">
                System-grade techwear / clearance spec
              </div>
            </div>
            <div class="text-right">
              <div class="font-sans text-xl md:text-2xl text-white">
                @if($price !== null)
                  {{ number_format((float) $price, 0, ',', '.') }} ₫
                @else
                  <span class="text-white/70">Liên hệ</span>
                @endif
              </div>
              @if($oldPrice !== null && $price !== null && (float) $oldPrice > (float) $price)
                <div class="text-sm text-white/40 line-through font-sans">
                  {{ number_format((float) $oldPrice, 0, ',', '.') }} ₫
                </div>
              @endif
            </div>
          </div>

          <div class="mt-6 grid grid-cols-1 gap-5">
            <div class="m56-cut-tight bg-black/70 p-4 m56-outline-weak">
              <div class="text-xs text-white/60 tracking-[0.28em] uppercase font-sans">Select Size</div>
              <div class="mt-3 flex flex-wrap gap-2">
                <template x-for="s in sizes" :key="'size-' + s">
                  <button
                    type="button"
                    class="px-3 py-2 text-sm font-sans tracking-wider uppercase m56-cut-tight bg-black/60 m56-outline-weak hover:m56-outline"
                    :class="selectedSize === s ? 'm56-glow text-yellow-300 border-yellow-400/70' : 'text-white/80'"
                    @click="selectedSize = s"
                  >
                    <span x-text="s"></span>
                  </button>
                </template>
                <div x-show="sizes.length === 0" class="text-white/50 text-sm font-sans">Không có size.</div>
              </div>
            </div>

            <div class="m56-cut-tight bg-black/70 p-4 m56-outline-weak">
              <div class="text-xs text-white/60 tracking-[0.28em] uppercase font-sans">Select Style</div>
              <div class="mt-3 flex flex-wrap gap-2">
                <template x-for="st in styles" :key="'style-' + st">
                  <button
                    type="button"
                    class="px-3 py-2 text-sm font-sans tracking-wider uppercase m56-cut-tight bg-black/60 m56-outline-weak hover:m56-outline"
                    :class="selectedStyle === st ? 'm56-glow text-yellow-300 border-yellow-400/70' : 'text-white/80'"
                    @click="selectedStyle = st"
                  >
                    <span x-text="st"></span>
                  </button>
                </template>
                <div x-show="styles.length === 0" class="text-white/50 text-sm font-sans">Không có style.</div>
              </div>
            </div>

            <div class="m56-cut-tight bg-black/70 p-4 m56-outline-weak">
              <div class="flex items-center justify-between gap-3">
                <div class="text-xs text-white/60 tracking-[0.28em] uppercase font-sans">Qty</div>
                <div class="flex items-center gap-2">
                  <button type="button" class="w-10 h-10 m56-cut-tight bg-black/60 m56-outline-weak hover:m56-outline text-white/80" @click="decQty()">-</button>
                  <input
                    type="number"
                    min="1"
                    class="w-20 h-10 bg-black/60 m56-cut-tight m56-outline-weak text-center text-white/90 font-sans tracking-wider focus:outline-none focus:m56-outline"
                    x-model.number="quantity"
                  >
                  <button type="button" class="w-10 h-10 m56-cut-tight bg-black/60 m56-outline-weak hover:m56-outline text-white/80" @click="incQty()">+</button>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 gap-3">
              <button
                type="button"
                class="m56-cut-cta m56-cta w-full py-4 font-sans text-sm md:text-base m56-glow-strong"
                @click="addToCart()"
              >
                BUY NOW
              </button>

              <div class="text-xs text-white/55 font-sans tracking-wide">
                <span class="text-yellow-300/90">ACCENT:</span> #ffcc00 ·
                <span class="text-white/70">Cyber cut</span> ·
                <span class="text-white/70">Vector-grade UI</span>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 m56-cut bg-black/70 m56-outline-weak p-6 md:p-7">
          <div class="text-xs text-white/60 tracking-[0.28em] uppercase font-sans">Description</div>
          <div class="mt-4 prose prose-invert max-w-none prose-p:text-white/75 prose-strong:text-white prose-a:text-yellow-300">
            {!! $product->description ?? '<p>Chưa có mô tả.</p>' !!}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios@1.6.8/dist/axios.min.js"></script>
<script>
  function m56ProductDetail(payload) {
    const safeImages = Array.isArray(payload.images) ? payload.images.filter(Boolean) : [];
    const firstImage = safeImages[0] || '{{ asset('images/no-image.jpg') }}';

    return {
      productId: payload.productId ?? null,
      productName: @json($product->name ?? 'Product'),
      images: safeImages,
      activeImage: firstImage,
      sizes: Array.isArray(payload.sizes) ? payload.sizes : [],
      styles: Array.isArray(payload.styles) ? payload.styles : [],
      selectedSize: null,
      selectedStyle: null,
      quantity: 1,
      imageLoaded: false,
      setActive(img) {
        if (!img) return;
        this.imageLoaded = false;
        this.activeImage = img;
      },
      isActiveThumb(img) {
        return img === this.activeImage;
      },
      fallbackImage(evt) {
        evt?.target?.setAttribute('src', '{{ asset('images/no-image.jpg') }}');
      },
      incQty() {
        const next = (Number(this.quantity) || 1) + 1;
        this.quantity = Math.max(1, next);
      },
      decQty() {
        const next = (Number(this.quantity) || 1) - 1;
        this.quantity = Math.max(1, next);
      },
      addToCart() {
        const data = {
          id: this.productId,
          size: this.selectedSize,
          style: this.selectedStyle,
          qty: Number(this.quantity) || 1
        };

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (window.axios && csrf) {
          window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf;
        }

        const url = '{{ url('/cart/add') }}';
        if (!window.axios) {
          alert('Axios chưa được nạp. Bạn có thể dùng Vite bundle hoặc CDN.');
          return;
        }

        return window.axios.post(url, data);
      }
    };
  }
</script>
@endsection
