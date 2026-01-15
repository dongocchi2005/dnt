@extends('frontend.layouts.app')

@section('title', 'DNT Store – Future of Technology')

@section('content')

    <!-- Particles Background -->
    <div class="particles-container">
        @for($i = 0; $i < 20; $i++)
            <div class="particle" style="left: {{ rand(0, 100) }}%; animation-duration: {{ rand(5, 15) }}s; animation-delay: {{ rand(0, 5) }}s;"></div>
        @endfor
    </div>

    <!-- 1. Hero Section -->
    <section class="cyber-hero">
        <div class="text-center z-10 relative">
            <h1 class="text-6xl md:text-8xl font-black mb-4">
                <span class="glitch glitch-dnt" data-text="DNT" style="color: var(--cyber-orange, #F37021) !important;">DNT</span>
                <span class="glitch" data-text="STORE">STORE</span>
            </h1>
            <p class="text-xl md:text-2xl text-cyan-100 mb-8 font-light tracking-widest uppercase">
                Công nghệ tương lai <span class="text-pink-500 mx-2">/</span> Dịch vụ đỉnh cao
            </p>
            <div class="flex justify-center gap-6 flex-wrap">
                <a href="{{ route('clearance.index') }}" class="cyber-btn">
                    Khám phá ngay
                </a>
                <a href="{{ route('booking.create') }}" class="cyber-btn cyber-btn-pink">
                    Đặt lịch sửa chữa
                </a>
            </div>
        </div>
    </section>

    <!-- 2. Categories Grid -->
    <section class="cyber-section">
        <h2 class="section-title text-4xl text-center mb-12 neon-text-cyan">
            Danh mục sản phẩm
        </h2>
        <div class="categories-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
            <!-- Laptop -->
            <a href="{{ route('clearance.index') }}" class="cyber-card p-6 flex flex-col items-center text-center group">
                <div class="text-6xl mb-4 text-cyan-400 group-hover:text-pink-500 transition-colors duration-300">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">TAI NGHE</h3>
                <p class="text-gray-400 text-sm">Hiệu năng cực đỉnh</p>
            </a>
            <!-- Phone -->
            <a href="{{ route('clearance.index') }}" class="cyber-card p-6 flex flex-col items-center text-center group">
                <div class="text-6xl mb-4 text-cyan-400 group-hover:text-pink-500 transition-colors duration-300">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">Smartphones</h3>
                <p class="text-gray-400 text-sm">Công nghệ dẫn đầu</p>
            </a>
            <!-- PC -->
            <a href="{{ route('clearance.index') }}" class="cyber-card p-6 flex flex-col items-center text-center group">
                <div class="text-6xl mb-4 text-cyan-400 group-hover:text-pink-500 transition-colors duration-300">
                    <i class="fas fa-microchip"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">PC Gaming</h3>
                <p class="text-gray-400 text-sm">Chiến mọi tựa game</p>
            </a>
            <!-- Accessories -->
            <a href="{{ route('clearance.index') }}" class="cyber-card p-6 flex flex-col items-center text-center group">
                <div class="text-6xl mb-4 text-cyan-400 group-hover:text-pink-500 transition-colors duration-300">
                    <i class="fas fa-headset"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">Gear & Audio</h3>
                <p class="text-gray-400 text-sm">Âm thanh sống động</p>
            </a>
        </div>
    </section>

    <!-- 3. Best Sellers -->
    <section class="cyber-section relative">
        <div class="absolute -left-20 top-1/4 w-96 h-96 bg-cyan-500 rounded-full mix-blend-screen filter blur-[128px] opacity-20 pointer-events-none"></div>
        <div class="absolute -right-20 bottom-1/4 w-96 h-96 bg-pink-500 rounded-full mix-blend-screen filter blur-[128px] opacity-20 pointer-events-none"></div>

        <h2 class="section-title text-4xl text-center mb-12 neon-text-pink">
            Sản phẩm Hot
        </h2>
        <div class="product-grid grid grid-cols-1 min-[360px]:grid-cols-2 lg:grid-cols-4">
            @forelse($bestSellers as $product)
                <x-product-card :product="$product" variant="home" />
            @empty
                <div class="col-span-full text-center py-12 border border-dashed border-gray-700 rounded">
                    <i class="fas fa-box-open text-4xl text-gray-600 mb-4"></i>
                    <p class="text-gray-500">Dữ liệu đang được nạp...</p>
                </div>
            @endforelse
        </div>
    </section>

    <!-- 4. Services -->
    <section class="cyber-section">
        <h2 class="section-title text-4xl text-center mb-12 neon-text-cyan">
            Dịch vụ kỹ thuật
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @forelse($services as $service)
                <div class="cyber-card p-8 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-20 text-8xl font-bold text-gray-700 select-none -mr-4 -mt-4">
                        {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </div>
                    <div class="cyber-service-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 uppercase tracking-wider">{{ $service->name }}</h3>
                    <p class="text-gray-400 mb-6 line-clamp-3">{{ $service->description }}</p>
                    <div class="flex items-center justify-between mt-auto">
                        <span class="text-cyan-400 font-bold text-xl">{{ $service->price ? number_format($service->price) . 'đ' : 'Liên hệ' }}</span>
                        <a href="{{ route('booking.create') }}" class="text-sm uppercase tracking-widest text-white hover:text-pink-500 transition-colors">
                            Đặt lịch <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            @empty
                 <div class="col-span-full text-center py-8">
                    <p>Dịch vụ đang được cập nhật.</p>
                </div>
            @endforelse
        </div>
    </section>

    <!-- 5. Trust / Features -->
    <section class="cyber-section trust-section">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            @forelse($features as $feature)
                <div class="feature-item p-4">
                    <div class="text-4xl text-cyan-400 mb-4">
                         @if($feature->icon)
                            <i class="{{ $feature->icon }}"></i>
                        @else
                            <i class="fas fa-check-circle"></i>
                        @endif
                    </div>
                    <h3 class="font-bold text-lg uppercase tracking-wide mb-2">{{ $feature->title }}</h3>
                    <p class="text-xs text-gray-500 uppercase tracking-widest">{{ $feature->description }}</p>
                </div>
            @empty
                 <div class="feature-item">
                    <i class="fas fa-shield-alt text-4xl text-cyan-400 mb-4"></i>
                    <h3 class="font-bold text-lg mt-2">Bảo hành uy tín</h3>
                </div>
                <div class="feature-item">
                    <i class="fas fa-shipping-fast text-4xl text-cyan-400 mb-4"></i>
                    <h3 class="font-bold text-lg mt-2">Giao hàng nhanh</h3>
                </div>
                <div class="feature-item">
                    <i class="fas fa-headset text-4xl text-cyan-400 mb-4"></i>
                    <h3 class="font-bold text-lg mt-2">Hỗ trợ 24/7</h3>
                </div>
                <div class="feature-item">
                    <i class="fas fa-undo text-4xl text-cyan-400 mb-4"></i>
                    <h3 class="font-bold text-lg mt-2">Đổi trả dễ dàng</h3>
                </div>
            @endforelse
        </div>
    </section>

    <!-- 6. Footer CTA -->
    <section class="cyber-footer-cta py-20 px-4 text-center">
        <div class="relative z-10 max-w-4xl mx-auto">
            <h2 class="text-4xl md:text-5xl font-black mb-6 uppercase tracking-widest">
                Sẵn sàng nâng cấp <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-pink-500">trải nghiệm?</span>
            </h2>
            <p class="text-gray-400 mb-10 text-lg max-w-2xl mx-auto">
                Tham gia vào hệ sinh thái công nghệ của DNT Store ngay hôm nay.
            </p>
            <div class="flex justify-center gap-6 flex-wrap">
                <a href="{{ route('register') }}" class="cyber-btn">
                    Đăng ký thành viên
                </a>
                <a href="{{ route('contact') }}" class="cyber-btn cyber-btn-pink">
                    Liên hệ tư vấn
                </a>
            </div>
        </div>
    </section>

@endsection
