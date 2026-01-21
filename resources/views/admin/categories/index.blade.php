@extends('layouts.admin')

@section('page-title','Quản lý danh mục')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="noti-cyber p-4 border-l-4 border-green-500">
            <div class="text-green-400 font-bold flex items-center">
                <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="noti-cyber p-4 border-l-4 border-red-500">
            <div class="text-red-400 font-bold flex items-center">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i>{{ session('error') }}
            </div>
        </div>
    @endif

    <div class="cyber-panel p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-bl font-display neon">Danh mục sản phẩm</h1>
                <p class="text-bl/60 mt-1">Quản lý các danh mục sản phẩm của cửa hàng</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" 
               class="cyber-btn bg-blue-600 hover:bg-blue-500 text-white">
                <i class="fa-solid fa-plus"></i> Thêm danh mục mới
            </a>
        </div>
    </div>

    <div class="cyber-panel">
        <div class="admin-panel-head">
            <div class="font-bold text-bl text-base">Bộ lọc</div>
            <div class="admin-panel-head__meta text-bl/60 text-sm">Tìm theo tên/mô tả, lọc danh mục có sản phẩm</div>
        </div>
        <div class="p-4">
            <form method="GET" action="{{ route('admin.categories.index') }}" class="admin-form-grid">
                <div class="admin-form-field admin-form-field--full">
                    <label class="sr-only" for="categorySearch">Tìm danh mục</label>
                    <input id="categorySearch" type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo tên/mô tả..." class="admin-input" />
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="categoryHasProducts">Sản phẩm</label>
                    <select id="categoryHasProducts" name="has_products" class="admin-input">
                        <option value="">Tất cả</option>
                        <option value="1" {{ request('has_products')==='1' ? 'selected' : '' }}>Có sản phẩm</option>
                        <option value="0" {{ request('has_products')==='0' ? 'selected' : '' }}>Chưa có sản phẩm</option>
                    </select>
                </div>
                <div class="admin-form-field">
                    <div class="admin-form-actions admin-form-actions--full">
                        <button type="submit" class="cyber-btn admin-btn bg-blue-600 hover:bg-blue-500 text-white flex items-center justify-center gap-1">
                            Lọc
                        </button>
                        <a href="{{ route('admin.categories.index') }}"
                           class="admin-btn admin-btn-full py-2 border border-white/10 rounded-lg text-sm text-bl/60 text-center hover:bg-white/5">
                            Xóa
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="cyber-panel overflow-hidden">
        <div class="admin-table-mobile-hide overflow-x-auto hidden sm:block">
            <table class="min-w-full text-sm">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider w-12">ID</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider min-w-48">Tên</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider min-w-64">Mô tả</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider w-32">Số sản phẩm</th>
                        <th class="px-6 py-3 text-right font-bold text-bl/50 uppercase tracking-wider w-40">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($categories as $category)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 text-bl/60 font-medium">{{ $category->id }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-bl text-base">{{ $category->name }}</div>
                            </td>
                            <td class="px-6 py-4 text-bl/70">{{ Str::limit($category->description, 60) }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20 font-bold text-xs">
                                    {{ $category->products->count() }} sản phẩm
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.categories.show', $category) }}" 
                                       class="text-blue-400 hover:text-blue-300 font-medium transition-colors">Xem</a>
                                    <a href="{{ route('admin.categories.edit', $category) }}" 
                                       class="text-blue-400 hover:text-blue-300 font-medium transition-colors">Sửa</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline-block" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300 font-medium transition-colors">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-bl/40 italic">Không có danh mục nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-mobile-cards px-4 py-4 block sm:hidden">
            @forelse($categories as $category)
                <div class="admin-mobile-card">
                    <div class="admin-mobile-card__head">
                        <div class="admin-mobile-card__title text-bl">
                            {{ $category->name }}
                        </div>
                        <div class="admin-mobile-card__meta">
                            #{{ $category->id }}
                        </div>
                    </div>

                    <div class="admin-mobile-card__body">
                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Mô tả</div>
                            <div class="admin-mobile-field__value text-bl/80">{{ $category->description ?: '-' }}</div>
                        </div>

                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Sản phẩm</div>
                            <div class="admin-mobile-field__value">
                                <span class="px-2 py-1 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20 font-bold text-xs">
                                    {{ $category->products->count() }} sản phẩm
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="admin-mobile-actions">
                        <a href="{{ route('admin.categories.show', $category) }}"
                           class="admin-action-btn border border-white/10 rounded-lg text-sm font-medium text-bl/80 bg-white/5 hover:bg-white/10 hover:text-blue-400 transition-colors flex items-center justify-center gap-2 w-full">
                            <i class="fas fa-eye text-bl/40"></i>
                            <span class="admin-action-label">Xem</span>
                        </a>
                        <a href="{{ route('admin.categories.edit', $category) }}"
                           class="admin-action-btn border border-white/10 rounded-lg text-sm font-medium text-bl/80 bg-white/5 hover:bg-white/10 hover:text-blue-400 transition-colors flex items-center justify-center gap-2 w-full">
                            <i class="fas fa-pen text-bl/40"></i>
                            <span class="admin-action-label">Sửa</span>
                        </a>
                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="w-full" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="admin-action-btn border border-white/10 rounded-lg text-sm font-medium text-red-300 bg-white/5 hover:bg-white/10 transition-colors flex items-center justify-center gap-2 w-full">
                                <i class="fas fa-trash text-red-300/70"></i>
                                <span class="admin-action-label">Xóa</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="admin-mobile-card">
                    <div class="text-center text-bl/40 italic">Không có danh mục nào.</div>
                </div>
            @endforelse
        </div>

        @if($categories->hasPages())
            <div class="px-6 py-4 border-t border-white/10 bg-white/5">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
