@extends('frontend.layouts.app')

@section('content')
<div class="cy-products-page min-h-screen bg-[var(--cy-bg-main)] text-[var(--cy-text-main)] font-sans">
    
    <!-- HEADER SECTION -->
    <div class="cy-hero relative overflow-hidden">
        <div class="cy-container relative z-10 flex flex-col items-center justify-center text-center h-full">
            <nav class="text-sm text-[var(--cy-text-sub)] mb-4 uppercase tracking-widest flex items-center gap-2">
                <a href="{{ route('home') }}" class="hover:text-[var(--cy-accent)] transition-colors">Home</a>
                <span>/</span>
                <span class="text-[var(--cy-accent)]">Clearance</span>
            </nav>
            <h1 class="text-4xl md:text-6xl font-black mb-2 tracking-tight uppercase glitch-text" data-text="CLEARANCE SHOP">
              SẢN PHẨM 
            </h1>
            <p class="text-lg md:text-xl text-[var(--cy-text-sub)] max-w-2xl">
                Săn hàng hiệu - Giá cực tốt - Số lượng có hạn
            </p>
        </div>
        <!-- Decorative Elements -->
        <div class="absolute top-0 left-0 w-full h-full pointer-events-none overflow-hidden">
             <div class="absolute top-1/4 left-10 w-32 h-32 bg-[var(--cy-accent)] opacity-10 blur-3xl rounded-full"></div>
             <div class="absolute bottom-1/4 right-10 w-40 h-40 bg-pink-600 opacity-10 blur-3xl rounded-full"></div>
        </div>
    </div>

    <div class="cy-container py-8">
        
        <!-- MAIN FILTER FORM -->
        <form id="cy-filter-form" action="{{ route('clearance.index') }}" method="GET">
            
            <div class="cy-grid-layout">
                
                <!-- SIDEBAR (DESKTOP) -->
                <aside class="hidden lg:block">
                    <div class="cy-filter-sidebar">
                        <!-- Categories -->
                        <div class="cy-filter-group">
                            <h4>Danh mục</h4>
                            <div class="space-y-2 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                                <label class="cy-checkbox-label">
                                    <input type="radio" name="category" value="all" class="cy-checkbox" 
                                        {{ request('category') == 'all' || !request('category') ? 'checked' : '' }}
                                        onchange="this.form.submit()">
                                    <span>Tất cả</span>
                                </label>
                                @foreach($categories as $cat)
                                    <label class="cy-checkbox-label">
                                        <input type="radio" name="category" value="{{ $cat['value'] }}" class="cy-checkbox"
                                            {{ request('category') == $cat['value'] ? 'checked' : '' }}
                                            onchange="this.form.submit()">
                                        <span>{{ $cat['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div class="cy-filter-group">
                            <h4>Khoảng giá</h4>
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <input type="number" name="min_price" placeholder="Min" 
                                        value="{{ request('min_price') }}"
                                        class="w-full bg-[var(--cy-bg-main)] border border-[var(--cy-border)] rounded px-3 py-2 text-sm focus:outline-none focus:border-[var(--cy-accent)] placeholder-gray-500">
                                    <span class="text-[var(--cy-text-sub)]">-</span>
                                    <input type="number" name="max_price" placeholder="Max" 
                                        value="{{ request('max_price') }}"
                                        class="w-full bg-[var(--cy-bg-main)] border border-[var(--cy-border)] rounded px-3 py-2 text-sm focus:outline-none focus:border-[var(--cy-accent)] placeholder-gray-500">
                                </div>
                                <button type="submit" class="cy-btn-secondary w-full text-xs uppercase tracking-wider">Áp dụng</button>
                            </div>
                        </div>

                        <!-- Other Filters -->
                        <div class="cy-filter-group">
                            <h4>Trạng thái</h4>
                            <label class="cy-checkbox-label">
                                <input type="checkbox" name="in_stock" value="1" class="cy-checkbox"
                                    {{ request('in_stock') ? 'checked' : '' }}
                                    onchange="this.form.submit()">
                                <span>Chỉ hiện còn hàng</span>
                            </label>
                        </div>
                    </div>
                </aside>

                <!-- MAIN CONTENT Area -->
                <main>
                    <!-- TOOLBAR -->
                    <div class="cy-toolbar">
                        <!-- Left: Search -->
                        <div class="flex-1 w-full md:w-auto relative">
                            <input type="text" id="cy-search-input" name="search" value="{{ request('search') }}"
                                placeholder="Tìm kiếm sản phẩm..." 
                                class="w-full md:max-w-xs bg-[var(--cy-bg-main)] border border-[var(--cy-border)] rounded pl-10 pr-4 py-2 text-sm focus:outline-none focus:border-[var(--cy-accent)] placeholder-gray-500">
                            <svg class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-[var(--cy-text-sub)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>

                        <!-- Right: Actions -->
                        <div class="flex items-center gap-3 mt-4 md:mt-0 w-full md:w-auto justify-between md:justify-end">
                            <!-- Mobile Filter Toggle -->
                            <button type="button" id="cy-filter-toggle" class="lg:hidden flex items-center gap-2 text-sm font-medium hover:text-[var(--cy-accent)]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                                Filter
                            </button>

                            <!-- Sort -->
                            <div class="relative">
                                <select id="cy-sort-select" name="sort" class="appearance-none bg-[var(--cy-bg-main)] border border-[var(--cy-border)] rounded px-4 py-2 pr-8 text-sm focus:outline-none focus:border-[var(--cy-accent)] cursor-pointer">
                                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Mới nhất</option>
                                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Giá thấp - cao</option>
                                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Giá cao - thấp</option>
                                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Tên A-Z</option>
                                </select>
                                <svg class="w-4 h-4 absolute right-2 top-1/2 transform -translate-y-1/2 text-[var(--cy-text-sub)] pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>

                            <!-- View Toggle (Desktop) -->
                            <div class="hidden md:flex items-center gap-1 border-l border-gray-700 pl-3 ml-2">
                                <button type="button" class="cy-view-btn p-1 hover:text-[var(--cy-accent)] transition-colors" data-cols="2" title="2 Cột">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                </button>
                                <button type="button" class="cy-view-btn p-1 hover:text-[var(--cy-accent)] transition-colors text-[var(--cy-accent)]" data-cols="4" title="4 Cột">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Active Chips -->
                    @if(request('category') && request('category') !== 'all' || request('min_price') || request('max_price') || request('in_stock'))
                    <div class="flex flex-wrap gap-2 mb-6">
                         @if(request('category') && request('category') !== 'all')
                            <span class="text-xs px-2 py-1 rounded border border-[var(--cy-accent)] text-[var(--cy-accent)] flex items-center gap-1">
                                Category: {{ request('category') }}
                                <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" class="hover:text-white">×</a>
                            </span>
                         @endif
                         <a href="{{ route('clearance.index') }}" class="text-xs underline hover:text-[var(--cy-accent)] py-1">Clear All</a>
                    </div>
                    @endif

                    <!-- PRODUCT GRID -->
                    <div id="cy-product-grid" class="cy-product-list transition-all duration-300">
                        @forelse($products as $product)
                            <x-product-card :product="$product" variant="index" :show-badges="true" :show-actions="true" />
                        @empty
                            <div class="col-span-full text-center py-20">
                                <div class="inline-block p-6 rounded-full bg-[var(--cy-bg-card)] border border-[var(--cy-border)] mb-4">
                                    <svg class="w-12 h-12 text-[var(--cy-text-sub)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <h3 class="text-2xl font-bold mb-2 text-[var(--cy-text-main)]">Không tìm thấy sản phẩm</h3>
                                <p class="text-[var(--cy-text-sub)]">Vui lòng thử bộ lọc khác.</p>
                                <a href="{{ route('clearance.index') }}" class="inline-block mt-4 text-[var(--cy-accent)] hover:underline">Xóa bộ lọc</a>
                            </div>
                        @endforelse
                    </div>

                    <!-- PAGINATION -->
                    <div class="cy-pagination mt-12">
                        {{ $products->links() }}
                    </div>

                </main>
            </div>
            
            <!-- MOBILE FILTER DRAWER -->
            <div id="cy-filter-drawer" class="cy-filter-drawer">
                <div class="flex justify-between items-center mb-6 border-b border-[var(--cy-border)] pb-4">
                    <h3 class="text-xl font-bold text-[var(--cy-text-main)]">BỘ LỌC</h3>
                    <button type="button" id="cy-filter-close" class="text-[var(--cy-text-sub)] hover:text-[var(--cy-accent)]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="space-y-6">
                     <!-- Categories -->
                     <div class="cy-filter-group">
                        <h4>Danh mục</h4>
                        <div class="space-y-2">
                            <label class="cy-checkbox-label">
                                <input type="radio" name="category" value="all" class="cy-checkbox" 
                                    {{ request('category') == 'all' || !request('category') ? 'checked' : '' }}>
                                <span>Tất cả</span>
                            </label>
                            @foreach($categories as $cat)
                                <label class="cy-checkbox-label">
                                    <input type="radio" name="category" value="{{ $cat['value'] }}" class="cy-checkbox"
                                        {{ request('category') == $cat['value'] ? 'checked' : '' }}>
                                    <span>{{ $cat['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="cy-filter-group">
                        <h4>Khoảng giá</h4>
                        <div class="space-y-3">
                             <input type="number" name="min_price" placeholder="Min" 
                                value="{{ request('min_price') }}"
                                class="w-full bg-[var(--cy-bg-main)] border border-[var(--cy-border)] rounded px-3 py-2 text-sm">
                             <input type="number" name="max_price" placeholder="Max" 
                                value="{{ request('max_price') }}"
                                class="w-full bg-[var(--cy-bg-main)] border border-[var(--cy-border)] rounded px-3 py-2 text-sm">
                        </div>
                    </div>

                    <!-- Stock -->
                     <div class="cy-filter-group">
                        <h4>Trạng thái</h4>
                        <label class="cy-checkbox-label">
                            <input type="checkbox" name="in_stock" value="1" class="cy-checkbox"
                                {{ request('in_stock') ? 'checked' : '' }}>
                            <span>Chỉ hiện còn hàng</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="cy-btn-primary w-full mt-4">Áp dụng</button>
                </div>
            </div>
            
            <div id="cy-filter-overlay" class="cy-drawer-overlay"></div>

        </form>
    </div>
</div>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: var(--cy-border);
    border-radius: 4px;
}
</style>
@endsection
