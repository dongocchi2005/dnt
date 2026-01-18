@extends('frontend.layouts.app')

@section('content')
<div class="particles-container">
    @for ($i = 0; $i < 30; $i++)
        <div class="particle" style="left: {{ rand(0, 100) }}%; animation-duration: {{ rand(5, 15) }}s; animation-delay: {{ rand(0, 5) }}s;"></div>
    @endfor
</div>

<!-- Hero -->
<div class="cyber-hero-small">
    <div>
        <h1 class="glitch text-5xl md:text-7xl font-bold mb-4" data-text="BẢN TIN">BẢN TIN</h1>
        <p class="text-xl md:text-2xl text-gray-300 neon-text-cyan">Tin tức công nghệ và mẹo sửa chữa</p>
    </div>
</div>

<!-- Blog Grid -->
<div class="container mx-auto px-4 py-16">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($posts ?? [] as $post)
                <div class="cyber-card flex flex-col h-full">
                    <img src="{{ $post->image ?? asset('image/bg.jpg') }}" class="w-full h-48 object-cover mb-4 border border-cyan-900/50">
                    <div class="flex-1">
                        <div class="text-xs text-cyan-400 mb-2 font-mono">
                            [{{ $post->created_at->format('Y-m-d') ?? '2026-01-14' }}] // SYSTEM_LOG
                        </div>
                        <h3 class="text-xl font-bold mb-3 text-white hover:text-cyan-400 transition-colors">
                            <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title ?? 'New Cyber-Implant Technology Released' }}</a>
                        </h3>
                        <p class="text-gray-400 text-sm mb-4 line-clamp-3">
                            {{ $post->excerpt ?? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.' }}
                        </p>
                    </div>
                    <a href="{{ route('blog.show', $post->slug) }}" class="text-pink-500 hover:text-pink-400 font-bold text-sm tracking-wider mt-auto">
XEM NGAY
                    </a>
                </div>
            @empty
                <div class="col-span-full flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-24 h-24 rounded-full bg-white/5 border border-white/10 flex items-center justify-center mb-6 animate-pulse">
                        <i class="fas fa-satellite-dish text-4xl text-cyan-500/50"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-2 neon-text-pink">NO_DATA_FOUND</h3>
                    <p class="text-gray-400 font-mono text-sm max-w-md">
                        // Dữ liệu tin tức đang được cập nhật từ máy chủ.
                        <br>Vui lòng quay lại sau.
                    </p>
                </div>
            @endforelse
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Search -->
            <div class="cyber-card">
                <h3 class="text-lg font-bold mb-4 neon-text-cyan">SEARCH_DATABASE</h3>
                <div class="relative">
                    <input type="text" class="cyber-input" placeholder="Enter keywords...">
                    <button class="absolute right-2 top-2 text-cyan-400"><i class="fas fa-search"></i></button>
                </div>
            </div>

            <!-- Categories -->
            <div class="cyber-card">
                <h3 class="text-lg font-bold mb-4 neon-text-pink">DANH MỤC</h3>
                <ul class="space-y-2 font-mono text-sm text-gray-300">
                    <li class="hover:text-cyan-400 cursor-pointer flex justify-between">
                        <span>> PHẦN CỨNG </span> <span>[42]</span>
                    </li>
                    <li class="hover:text-cyan-400 cursor-pointer flex justify-between">
                        <span>> PHẦN MỀM</span> <span>[15]</span>
                    </li>
                    <li class="hover:text-cyan-400 cursor-pointer flex justify-between">
                        <span>> MẸO SỮA CHỮA</span> <span>[89]</span>
                    </li>
                    <li class="hover:text-cyan-400 cursor-pointer flex justify-between">
                        <span>> VIDEO CHỮA CHỮA</span> <span>[03]</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
