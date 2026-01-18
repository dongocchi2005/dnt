@extends('frontend.layouts.app')

@section('content')
<div class="particles-container">
    @for ($i = 0; $i < 30; $i++)
        <div class="particle"
             style="left: {{ rand(0, 100) }}%;
                    animation-duration: {{ rand(5, 15) }}s;
                    animation-delay: {{ rand(0, 5) }}s;">
        </div>
    @endfor
</div>

<!-- Hero -->
<div class="cyber-hero-small">
    <div>
        <h1 class="glitch text-5xl md:text-7xl font-bold mb-4" data-text="LIÊN HỆ">LIÊN HỆ</h1>
        <p class="text-xl md:text-2xl text-gray-300 neon-text-cyan">Thiết lập kết nối an toàn</p>
    </div>
</div>

<div class="container mx-auto px-4 py-16">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Connection Hub (Replaces Form) -->
        <div class="cyber-card h-full flex flex-col">
            <h2 class="text-2xl font-bold mb-6 neon-text-pink text-center tracking-widest">TRUNG TÂM KẾT NỐI</h2>

            <div class="space-y-6 flex-1 flex flex-col justify-center">
                <!-- Status Indicator -->
                <div class="bg-black/40 p-6 rounded-lg border border-cyan-500/30 relative overflow-hidden group hover:border-cyan-500/60 transition-colors">
                    <div class="absolute -right-4 -top-4 opacity-10 transform rotate-12 group-hover:rotate-0 transition-transform duration-700">
                        <i class="fas fa-satellite-dish text-9xl text-cyan-400"></i>
                    </div>
                    
                    <h3 class="text-cyan-400 font-bold mb-2 font-mono text-lg flex items-center gap-2">
                        <i class="fas fa-terminal"></i> HỆ THỐNG TRỰC TUYẾN
                    </h3>
                    <p class="text-gray-300 text-sm mb-4 relative z-10">
                        Đội ngũ kỹ thuật viên DNT Store luôn sẵn sàng tiếp nhận tín hiệu từ bạn. Kết nối ngay để được hỗ trợ nhanh nhất.
                    </p>
                    
                    <div class="flex items-center gap-3 text-xs font-mono text-green-400 bg-green-500/10 w-fit px-3 py-1 rounded-full border border-green-500/20">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        OPERATIONAL // ONLINE
                    </div>
                </div>

                <!-- Social Links -->
                <div class="grid grid-cols-1 gap-4">
                    <a href="https://facebook.com" target="_blank" class="flex items-center p-4 bg-blue-600/5 border border-blue-500/30 rounded-lg hover:bg-blue-600/20 hover:border-blue-400 hover:shadow-[0_0_15px_rgba(37,99,235,0.3)] transition-all group">
                        <div class="w-12 h-12 flex items-center justify-center bg-blue-600/20 rounded-lg mr-4 group-hover:scale-110 transition-transform border border-blue-500/30">
                            <i class="fab fa-facebook-f text-2xl text-blue-400"></i>
                        </div>
                        <div>
                            <div class="text-blue-400 font-bold font-display tracking-wide group-hover:text-blue-300">FACEBOOK</div>
                            <div class="text-gray-500 text-xs font-mono group-hover:text-gray-400">Cập nhật tin tức & ưu đãi</div>
                        </div>
                        <i class="fas fa-external-link-alt ml-auto text-blue-500/50 group-hover:text-blue-400"></i>
                    </a>

                    <a href="https://zalo.me" target="_blank" class="flex items-center p-4 bg-cyan-600/5 border border-cyan-500/30 rounded-lg hover:bg-cyan-600/20 hover:border-cyan-400 hover:shadow-[0_0_15px_rgba(8,145,178,0.3)] transition-all group">
                        <div class="w-12 h-12 flex items-center justify-center bg-cyan-600/20 rounded-lg mr-4 group-hover:scale-110 transition-transform border border-cyan-500/30">
                            <i class="fas fa-comment-dots text-2xl text-cyan-400"></i>
                        </div>
                        <div>
                            <div class="text-cyan-400 font-bold font-display tracking-wide group-hover:text-cyan-300">ZALO CHAT</div>
                            <div class="text-gray-500 text-xs font-mono group-hover:text-gray-400">Tư vấn trực tiếp 24/7</div>
                        </div>
                        <i class="fas fa-external-link-alt ml-auto text-cyan-500/50 group-hover:text-cyan-400"></i>
                    </a>
                    
                    <a href="tel:0999888777" class="flex items-center p-4 bg-pink-600/5 border border-pink-500/30 rounded-lg hover:bg-pink-600/20 hover:border-pink-400 hover:shadow-[0_0_15px_rgba(219,39,119,0.3)] transition-all group">
                        <div class="w-12 h-12 flex items-center justify-center bg-pink-600/20 rounded-lg mr-4 group-hover:scale-110 transition-transform border border-pink-500/30">
                            <i class="fas fa-phone-alt text-2xl text-pink-400"></i>
                        </div>
                        <div>
                            <div class="text-pink-400 font-bold font-display tracking-wide group-hover:text-pink-300">HOTLINE</div>
                            <div class="text-gray-500 text-xs font-mono group-hover:text-gray-400">0999.888.777</div>
                        </div>
                        <div class="ml-auto w-2 h-2 rounded-full bg-pink-500 animate-pulse"></div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Info & Map -->
        <div class="space-y-8">
            <div class="cyber-card">
                <h3 class="text-xl font-bold mb-4 neon-text-cyan">VỊ TRÍ CỬA HÀNG</h3>

                <div class="space-y-4 text-gray-300">
                    <p class="flex items-start">
                        <i class="fas fa-map-marker-alt mt-1 mr-3 text-pink-500"></i>
                        <span>DNT STORE HCM — Sửa tai nghe, lấy liền và sửa đổi chính hãng</span>
                    </p>

                    <p class="flex items-center">
                        <i class="fas fa-phone-alt mr-3 text-pink-500"></i>
                        <span>+84 999 888 777</span>
                    </p>

                    <p class="flex items-center">
                        <i class="fas fa-envelope mr-3 text-pink-500"></i>
                        <span>admin@dntstore.cyber</span>
                    </p>
                </div>
            </div>

            <!-- Google Map (IFRAME) -->
            <div class="cyber-card relative overflow-hidden">
                {{-- Wrapper để iframe không bị tràn + có tỉ lệ đẹp --}}
                <div class="relative w-full overflow-hidden rounded-xl"
                     style="aspect-ratio: 16 / 9;">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.0507835059952!2d106.62746377363553!3d10.807422558624099!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752984d624166d%3A0xe2924f4e9a209b2d!2zRE5UIFNUT1JFIEhDTV9T4budYSB0YWkgbmdoZSBs4bqleSBsaeG7gW4gdsOgIHPhu61hIMSR4buTIGNoxqFpIGPDtG5nIG5naOG7hyBo4buTIGNow60gbWluaA!5e0!3m2!1svi!2s!4v1768443036662!5m2!1svi!2s"
                        width="100%"
                        height="100%"
                        style="border:0; position:absolute; inset:0;"
                        allowfullscreen
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>

                    {{-- Overlay cyber nhẹ để hợp theme (không chặn click) --}}
                    <div class="pointer-events-none absolute inset-0"
                         style="background:
                            radial-gradient(900px 520px at 70% 20%, rgba(0,243,255,.18), transparent 60%),
                            linear-gradient(180deg, rgba(255,0,255,.10), transparent 45%);
                            mix-blend-mode: screen;">
                    </div>

                    {{-- Grid overlay cyber (không chặn click) --}}
                    <div class="pointer-events-none absolute inset-0"
                         style="background-image: radial-gradient(var(--cyber-cyan) 1px, transparent 1px);
                                background-size: 22px 22px;
                                opacity: .18;">
                    </div>
                </div>

                {{-- Footer nhỏ dưới map (tuỳ chọn) --}}
                <div class="mt-4 flex items-center justify-between gap-3 text-xs">
                    <div class="font-mono text-cyan-300/90">
                        SATELLITE LINK: <span class="text-gray-300">ACTIVE</span>
                    </div>
                    <a class="font-mono text-pink-300 hover:text-pink-200 underline underline-offset-4"
                       href="https://www.google.com/maps"
                       target="_blank" rel="noopener">
                        OPEN MAP
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
