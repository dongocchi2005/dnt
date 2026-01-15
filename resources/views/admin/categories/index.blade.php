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

    <div class="cyber-panel overflow-hidden">
        <div class="overflow-x-auto">
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

        @if($categories->hasPages())
            <div class="px-6 py-4 border-t border-white/10 bg-white/5">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
