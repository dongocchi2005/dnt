@extends('frontend.layouts.app')

@section('title', $product->name)

@section('styles')
    @vite(['resources/css/pages/product-show.css'])
@endsection

@section('content')
<div class="cyber-product-page" data-product-id="{{ $product->id }}">
    <div class="container mx-auto px-4 py-8">
        <!-- Breadcrumbs -->
        <div class="text-gray-400 text-sm mb-6 font-mono">
            <a href="/" class="hover:text-cyan-400">HOME</a> / 
            <a href="#" class="hover:text-cyan-400">PRODUCTS</a> / 
            <span class="text-cyan-400">{{ $product->name }}</span>
        </div>

        <div class="pd-grid">
            <!-- Left: Gallery -->
            <div class="gallery-container">
                <div class="pd-media">
                    @php 
                        // Resolver URL logic
                        $imgUrl = function($path) {
                            if (!$path) return null;
                            if (Str::startsWith($path, ['http://', 'https://'])) return $path;
                            if (Str::startsWith($path, ['image/', '/image', 'images/', '/images'])) return asset($path);
                            if (Str::startsWith($path, ['storage/', '/storage'])) return asset($path);
                            return Storage::url($path);
                        };

                        // 1. Main Image (products.image)
                        $mainUrl = $imgUrl($product->image);
                        if (!$mainUrl) {
                            $mainUrl = asset('images/no-image.jpg');
                        }

                        // 2. Gallery Images (product_images relation)
                        $thumbUrls = collect([]);
                        if($product->images && $product->images->isNotEmpty()) {
                            $thumbUrls = $product->images->pluck('image')
                                ->filter()
                                ->map(fn($p) => $imgUrl($p))
                                ->unique()
                                ->reject(fn($u) => $u === $mainUrl) // Tránh trùng ảnh chính
                                ->values();
                        }
                    @endphp
                    
                    <img id="pd-main" 
                         src="{{ $mainUrl }}" 
                         alt="{{ $product->name }}" 
                         class="pd-main-img"
                         onerror="this.onerror=null;this.src='{{ asset('images/no-image.jpg') }}';">
                    
                    <!-- Cyber decorations -->
                    <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-cyan-400"></div>
                    <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-cyan-400"></div>
                    <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-cyan-400"></div>
                    <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-cyan-400"></div>
                </div>

                <div class="pd-thumbs">
                    <!-- Render main image as first thumb if needed, or just gallery -->
                    <!-- User requested: Thumbnails = lấy từ product_images.image -->
                    <!-- But usually main image is also part of gallery. User said "Thumbnails KHÔNG được trùng với ảnh chính" -->
                    <!-- So we only render product_images here -->
                    
                    @foreach($thumbUrls as $idx => $imgUrl)
                        <button class="pd-thumb {{ $idx === 0 ? 'is-active' : '' }}" data-img="{{ $imgUrl }}">
                            <img src="{{ $imgUrl }}" 
                                 alt="Thumb {{ $idx }}" 
                                 onerror="this.parentElement.remove()">
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Right: Info -->
            <div class="product-info-card">
                @php
                    $initialPrice = $initialVariant?->effective_price ?? ($product->display_price ?? $product->price ?? 0);
                    $initialOld = $initialVariant?->price ?? ($product->display_original_price ?? $product->original_price ?? null);
                    $initialStock = (int)($initialVariant?->stock ?? 0);
                    $initialSku = $initialVariant?->sku ?? null;
                @endphp
                <h1 class="product-title text-white">{{ $product->name }}</h1>
                <div class="product-sku">SKU: <span data-vp-sku>{{ $initialSku ?? 'N/A' }}</span></div>

                <div class="price-block" 
                     data-initial-price="{{ $initialPrice }}"
                     data-initial-stock="{{ $initialStock }}">
                    
                    <span class="current-price" id="product-price" data-vp-price>
                        {{ number_format((float)$initialPrice, 0, ',', '.') }} ₫
                    </span>
                    
                    @if($initialOld && $initialPrice && (float)$initialOld > (float)$initialPrice)
                        <span class="original-price" data-vp-old-price>
                            {{ number_format((float)$initialOld, 0, ',', '.') }} ₫
                        </span>
                    @endif
                </div>

                <div class="mb-4">
                    <span class="text-gray-400">Tình trạng: </span>
                    <span id="stock-status" data-vp-stock class="font-bold {{ $initialStock > 0 ? 'text-green-400' : 'text-red-500' }}">
                        {{ $initialStock > 0 ? ('Còn ' . $initialStock) : 'Hết hàng' }}
                    </span>
                </div>

                @include('frontend.products._variant-picker', [
                    'product' => $product,
                    'variantOptions' => $variantOptions ?? [],
                    'variantsPayload' => $variantsPayload ?? [],
                    'initialVariant' => $initialVariant ?? null,
                ])

                <!-- Actions -->
                <div class="actions-wrapper">
                    <div class="quantity-control">
                        <button class="qty-btn" id="btn-minus">-</button>
                        <input type="number" id="qty-input" class="qty-input" value="1" min="1">
                        <button class="qty-btn" id="btn-plus">+</button>
                    </div>

                    <button id="btn-add-to-cart" class="btn-add-cart" data-vp-cta>
                        <i class="fas fa-shopping-cart"></i> THÊM VÀO GIỎ
                    </button>
                </div>
                
                <div class="mt-4 text-xs text-gray-500">
                    <i class="fas fa-shield-alt text-cyan-400"></i> Bảo hành chính hãng 12 tháng
                    <span class="mx-2">|</span>
                    <i class="fas fa-truck text-cyan-400"></i> Free ship nội thành
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="cyber-tabs">
            <div class="tab-headers">
                <button class="tab-btn active" onclick="openTab(event, 'desc')">MÔ TẢ CHI TIẾT</button>
                <button class="tab-btn" onclick="openTab(event, 'spec')">THÔNG SỐ KỸ THUẬT</button>
                <button class="tab-btn" onclick="openTab(event, 'reviews')">ĐÁNH GIÁ</button>
            </div>

            <div id="desc" class="tab-content active">
                <div class="prose prose-invert max-w-none">
                    {!! $product->description !!}
                </div>
            </div>
            <div id="spec" class="tab-content">
                <p>Chưa có thông số kỹ thuật.</p>
            </div>
            <div id="reviews" class="tab-content">
                <p>Chưa có đánh giá nào.</p>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
@vite(['resources/js/pages/product-show.js'])
<script>
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tab-btn");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
    }
</script>
@endsection
