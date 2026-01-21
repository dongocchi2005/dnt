@extends('layouts.admin')

@section('page-title', 'Quản lý Sản phẩm')

@section('content')
<div class="space-y-6">
    <div class="cyber-panel">
        <div class="admin-panel-head">
            <div>
                <h1 class="text-2xl font-bold text-bl font-display neon">Quản lý Sản phẩm</h1>
                <p class="text-bl/60 mt-1">Danh sách sản phẩm đang kinh doanh</p>
            </div>
            <a href="{{ route('admin.products.create') }}"
               class="cyber-btn admin-btn bg-blue-600 hover:bg-blue-500 text-white flex items-center justify-center gap-2">
                <i class="fas fa-plus"></i> Thêm sản phẩm
            </a>
        </div>
    </div>

    <div class="cyber-panel">
        <div class="admin-panel-head">
            <div class="font-bold text-bl text-base">Bộ lọc</div>
            <div class="admin-panel-head__meta text-bl/60 text-sm">Lọc nhanh theo tên, danh mục, trạng thái</div>
        </div>
        <div class="p-4">
            <form method="GET" action="{{ route('admin.products.index') }}" class="admin-form-grid">
                <div class="admin-form-field admin-form-field--full">
                    <label class="sr-only" for="productSearch">Tìm sản phẩm</label>
                    <input id="productSearch" type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo tên..." class="admin-input" />
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="productCategory">Danh mục</label>
                    <select id="productCategory" name="category_id" class="admin-input">
                        <option value="">Tất cả danh mục</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string)request('category_id')===(string)$cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="productActive">Trạng thái</label>
                    <select id="productActive" name="is_active" class="admin-input">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" {{ request('is_active')==='1' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="0" {{ request('is_active')==='0' ? 'selected' : '' }}>Tạm ẩn</option>
                    </select>
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="productClearance">Clearance</label>
                    <select id="productClearance" name="is_clearance" class="admin-input">
                        <option value="">Tất cả</option>
                        <option value="1" {{ request('is_clearance')==='1' ? 'selected' : '' }}>Clearance</option>
                        <option value="0" {{ request('is_clearance')==='0' ? 'selected' : '' }}>Không</option>
                    </select>
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="productStock">Tồn kho</label>
                    <select id="productStock" name="stock" class="admin-input">
                        <option value="">Tất cả tồn kho</option>
                        <option value="in" {{ request('stock')==='in' ? 'selected' : '' }}>Còn hàng</option>
                        <option value="out" {{ request('stock')==='out' ? 'selected' : '' }}>Hết hàng</option>
                    </select>
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="productPriceMin">Giá từ</label>
                    <input id="productPriceMin" type="number" inputmode="numeric" name="price_min" value="{{ request('price_min') }}" placeholder="Giá từ..." class="admin-input" />
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="productPriceMax">Giá đến</label>
                    <input id="productPriceMax" type="number" inputmode="numeric" name="price_max" value="{{ request('price_max') }}" placeholder="Giá đến..." class="admin-input" />
                </div>
                <div class="admin-form-field">
                    <div class="admin-form-actions admin-form-actions--full">
                        <button type="submit" class="cyber-btn admin-btn bg-blue-600 hover:bg-blue-500 text-white flex items-center justify-center gap-1">
                            Lọc
                        </button>
                        <a href="{{ route('admin.products.index') }}"
                           class="admin-btn admin-btn-full py-2 border border-white/10 rounded-lg text-sm text-bl/60 text-center hover:bg-white/5">
                            Xóa
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="cyber-panel overflow-hidden">
        <div class="admin-panel-head">
            <div class="font-bold text-bl text-base">Danh sách</div>
            <div class="admin-panel-head__meta text-bl/60 text-sm">
                Tổng: <span class="text-blue-400 font-bold neon">{{ $products->total() ?? $products->count() }}</span>
            </div>
        </div>

        <div class="admin-table-wrap hidden sm:block">
            <table class="admin-table">
                <thead class="bg-white/5">
                    <tr>
                        <th class="text-left font-bold text-bl/50 uppercase tracking-wider w-20">Ảnh</th>
                        <th class="text-left font-bold text-bl/50 uppercase tracking-wider">Tên sản phẩm</th>
                        <th class="text-left font-bold text-bl/50 uppercase tracking-wider w-32">Giá bán</th>
                        <th class="text-left font-bold text-bl/50 uppercase tracking-wider w-32">Trạng thái</th>
                        <th class="text-right font-bold text-bl/50 uppercase tracking-wider w-32">Thao tác</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-white/5">
                    @forelse($products as $product)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td>
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}"
                                         alt="{{ $product->name }}"
                                         class="w-12 h-12 object-cover rounded-lg border border-white/10">
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-white/5 flex items-center justify-center text-bl/40 border border-white/10">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div class="font-bold text-bl text-base">{{ $product->name }}</div>
                                <div class="text-bl/50 text-xs mt-0.5 font-mono">SKU: {{ $product->sku ?? '---' }}</div>
                            </td>

                            <td class="font-bold text-bl neon">
                                {{ number_format($product->sale_price, 0, ',', '.') }} đ
                            </td>

                            <td>
                                @if($product->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-[0_0_10px_rgba(16,185,129,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 shadow-[0_0_5px_rgba(16,185,129,0.8)]"></span>
                                        Hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-gray-500/20 text-gray-400 border border-gray-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>
                                        Tạm ẩn
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="admin-actions justify-end">
                                    <a href="{{ route('admin.products.edit', $product->id) }}"
                                       class="admin-action-btn border border-white/10 bg-white/5 text-blue-400 hover:bg-blue-500/10 transition sm:mr-0">
                                        <i class="fas fa-edit"></i>
                                        <span class="admin-action-label">Sửa</span>
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.products.destroy', $product->id) }}"
                                          class="inline"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="admin-action-btn border border-red-500/20 bg-red-500/10 text-red-400 hover:bg-red-500/20 transition">
                                            <i class="fas fa-trash-alt"></i>
                                            <span class="admin-action-label">Xóa</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-bl/40">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fas fa-box-open text-4xl mb-3 opacity-50"></i>
                                    <p>Chưa có sản phẩm nào.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-mobile-cards block sm:hidden">
            @forelse($products as $product)
                <div class="admin-mobile-card">
                    <div class="admin-mobile-card__head">
                        <div class="admin-mobile-card__title text-bl">
                            {{ $product->name }}
                        </div>
                        <div class="admin-mobile-card__meta">
                            SKU: {{ $product->sku ?? '---' }}
                        </div>
                    </div>

                    <div class="admin-mobile-card__body">
                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Ảnh</div>
                            <div class="admin-mobile-field__value">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}"
                                         alt="{{ $product->name }}"
                                         class="w-16 h-16 object-cover rounded-lg border border-white/10">
                                @else
                                    <div class="w-16 h-16 rounded-lg bg-white/5 flex items-center justify-center text-bl/40 border border-white/10">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Giá bán</div>
                            <div class="admin-mobile-field__value font-bold text-bl neon">
                                {{ number_format($product->sale_price, 0, ',', '.') }} đ
                            </div>
                        </div>

                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Trạng thái</div>
                            <div class="admin-mobile-field__value">
                                @if($product->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-[0_0_10px_rgba(16,185,129,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 shadow-[0_0_5px_rgba(16,185,129,0.8)]"></span>
                                        Hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-gray-500/20 text-gray-400 border border-gray-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>
                                        Tạm ẩn
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="admin-mobile-actions">
                        <a href="{{ route('admin.products.edit', $product->id) }}"
                           class="admin-action-btn border border-white/10 rounded-lg text-sm font-medium text-blue-300 bg-white/5 hover:bg-white/10 transition-colors flex items-center justify-center gap-2 w-full">
                            <i class="fas fa-edit"></i>
                            <span class="admin-action-label">Sửa</span>
                        </a>
                        <form method="POST"
                              action="{{ route('admin.products.destroy', $product->id) }}"
                              class="w-full"
                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="admin-action-btn border border-red-500/20 rounded-lg text-sm font-medium text-red-300 bg-red-500/10 hover:bg-red-500/20 transition-colors flex items-center justify-center gap-2 w-full">
                                <i class="fas fa-trash-alt"></i>
                                <span class="admin-action-label">Xóa</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="admin-mobile-card">
                    <div class="text-center text-bl/40">Chưa có sản phẩm nào.</div>
                </div>
            @endforelse
        </div>

        @if($products->hasPages())
            <div class="admin-panel-footer">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
