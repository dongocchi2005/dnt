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

    <div class="cyber-panel overflow-hidden">
        <div class="admin-panel-head">
            <div class="font-bold text-bl text-base">Danh sách</div>
            <div class="admin-panel-head__meta text-bl/60 text-sm">
                Tổng: <span class="text-blue-400 font-bold neon">{{ $products->total() ?? $products->count() }}</span>
            </div>
        </div>

        <div class="admin-table-wrap">
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

        @if($products->hasPages())
            <div class="admin-panel-footer">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
