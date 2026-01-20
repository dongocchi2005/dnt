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
        <!-- Contact Form -->
        <div class="cyber-card">
            <h2 class="text-2xl font-bold mb-6 neon-text-pink">GỬI TÍN HIỆU</h2>

            <form action="#" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-cyan-400 text-sm font-bold mb-2 font-mono">USER_ID (NAME)</label>
                    <input type="text" name="name" class="cyber-input" placeholder="Enter your designation">
                </div>

                <div>
                    <label class="block text-cyan-400 text-sm font-bold mb-2 font-mono">COMMS_LINK (EMAIL)</label>
                    <input type="email" name="email" class="cyber-input" placeholder="Enter secure email">
                </div>

                <div>
                    <label class="block text-cyan-400 text-sm font-bold mb-2 font-mono">DATA_PACKET (MESSAGE)</label>
                    <textarea rows="5" name="message" class="cyber-input" placeholder="Enter transmission content..."></textarea>
                </div>

                <button type="submit" class="cyber-btn w-full text-center">
                    UPLOAD DATA
                </button>
            </form>
        </div>

        <!-- Info & Map -->
        <div class="space-y-8">
            <div class="cyber-card">
                <h3 class="text-xl font-bold mb-4 neon-text-cyan">VỊ TRÍ CỬA HÀNG</h3>

                <div class="space-y-4 text-gray-300">
                    <p class="flex items-start">
                        <i class="fas fa-map-marker-alt mt-1 mr-3 text-[rgb(var(--cyber-orange-rgb))]"></i>
                        <span>DNT STORE HCM — Sửa tai nghe, lấy liền và sửa đổi chính hãng</span>
                    </p>

                    <p class="flex items-center">
                        <i class="fas fa-phone-alt mr-3 text-[rgb(var(--cyber-orange-rgb))]"></i>
                        <span>+84 999 888 777</span>
                    </p>

                    <p class="flex items-center">
                        <i class="fas fa-envelope mr-3 text-[rgb(var(--cyber-orange-rgb))]"></i>
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
                            linear-gradient(180deg, rgb(var(--cyber-orange-rgb) / .10), transparent 45%);
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
                    <a class="font-mono text-[rgb(var(--cyber-orange-rgb))] hover:text-[rgb(var(--cyber-orange-rgb))] underline underline-offset-4"
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
