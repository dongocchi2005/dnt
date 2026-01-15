@extends('layouts.admin')

@section('title', 'Knowledge Base - DNT Store')
@section('page-title', 'Knowledge Base')

@section('content')
<div class="space-y-6">
    <div class="cyber-panel p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-bl font-display neon">Knowledge Base</h1>
                <p class="text-bl/60 mt-1">Quản lý FAQ, chính sách và nội dung tri thức.</p>
            </div>
            <a href="{{ route('admin.knowledge-base.create') }}" 
               class="cyber-btn bg-blue-600 hover:bg-blue-500 text-white">
                <i class="fa-solid fa-plus"></i> Thêm mới
            </a>
        </div>

        @if(session('error'))
            <div class="mb-4 rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-400 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        @if(!empty($tableMissing))
            <div class="mb-4 rounded-xl border border-yellow-500/20 bg-yellow-500/10 p-4 text-sm text-yellow-400 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i> Bảng `knowledge_base` chưa tồn tại. Vui lòng chạy `php artisan migrate`.
            </div>
        @endif

        <form method="GET" class="flex gap-2">
            <input type="text" name="q" value="{{ $search ?? '' }}" 
                   class="flex-1 bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-bl placeholder-white/30 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all" 
                   placeholder="Tìm theo tiêu đề, nội dung...">
            <button type="submit" class="cyber-btn bg-white/10 hover:bg-white/20 text-bl border border-white/10">
                <i class="fa-solid fa-magnifying-glass"></i> Tìm
            </button>
        </form>
    </div>

    <div class="cyber-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider">Tiêu đề</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider">Danh mục</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider">Loại</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-3 text-right font-bold text-bl/50 uppercase tracking-wider">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($items as $item)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 font-bold text-bl">{{ $item->title }}</td>
                            <td class="px-6 py-4 text-bl/80">{{ $item->category }}</td>
                            <td class="px-6 py-4 text-bl/60">{{ $item->source_type }}</td>
                            <td class="px-6 py-4">
                                @if($item->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-white/10 text-bl/60 border border-white/10">Hidden</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.knowledge-base.edit', $item) }}" 
                                       class="px-3 py-1.5 rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20 hover:bg-blue-500/20 transition-colors text-xs font-bold">
                                        Sửa
                                    </a>
                                    <form method="POST" action="{{ route('admin.knowledge-base.destroy', $item) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="px-3 py-1.5 rounded-lg bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20 transition-colors text-xs font-bold" 
                                                onclick="return confirm('Xoá mục này?')">
                                            Xoá
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-6 py-10 text-center text-bl/40 italic" colspan="5">Chưa có dữ liệu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-white/10 bg-white/5">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection
