<header class="w-full bg-black/70 backdrop-blur-md">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="text-2xl font-extrabold tracking-wide">
            <span style="color: var(--cyber-orange, #F37021) !important;">DNT</span><span class="text-white">Store</span>
        </a>

        {{-- Menu giữa --}}
        <nav class="hidden md:flex items-center gap-10 text-white/80 font-medium">
            <a href="{{ route('home') }}"
               class="hover:text-white transition {{ request()->routeIs('home') ? 'text-white' : '' }}">
                Trang chủ
            </a>

            <a href="{{ route('services') }}"
               class="hover:text-white transition {{ request()->routeIs('services') ? 'text-white' : '' }}">
                Dịch vụ
            </a>

            <a href="{{ route('contact') }}"
               class="hover:text-white transition {{ request()->routeIs('contact') ? 'text-white' : '' }}">
                Liên hệ
            </a>
        </nav>

        {{-- Nút phải --}}
        <div class="flex items-center gap-6">
            <a href="{{ route('booking.create') }}"
               class="px-6 py-2 rounded-xl bg-cyan-500 text-black font-semibold hover:bg-cyan-400 transition">
                Đặt lịch
            </a>

            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-white/80 hover:text-white transition">
                        Đăng xuất
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-white/80 hover:text-white transition">Đăng nhập</a>
            @endauth
        </div>

    </div>
</header>
