@extends('layouts.admin')

@section('page-title', 'Quản lý Bài viết')

@section('content')
<div class="space-y-6">
    <div class="cyber-panel p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-bl font-display neon">Quản lý Bài viết</h1>
                <p class="text-bl/60 mt-1">Tin tức, blog và thông báo</p>
            </div>

            <a href="{{ route('admin.posts.create') }}"
               class="cyber-btn bg-blue-600 hover:bg-blue-500 text-white">
                <i class="fa-solid fa-plus"></i> Thêm bài viết mới
            </a>
        </div>
    </div>

    <div class="cyber-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider w-24">Ảnh</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider min-w-64">Tiêu đề</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider w-32">Trạng thái</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider w-32">Ngày tạo</th>
                        <th class="px-6 py-3 text-right font-bold text-bl/50 uppercase tracking-wider w-40">Hành động</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-white/5">
                    @forelse($posts as $post)
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4">
                            @if($post->image)
                                <img src="{{ asset($post->image) }}"
                                     alt="{{ $post->title }}"
                                     class="w-16 h-10 object-cover rounded border border-white/20 shadow-sm">
                            @else
                                <div class="w-16 h-10 rounded border border-white/10 bg-white/5 flex items-center justify-center text-bl/30">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            <div class="font-bold text-bl text-base line-clamp-2">
                                {{ $post->title }}
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($post->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-[0_0_10px_rgba(16,185,129,0.2)]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 shadow-[0_0_5px_rgba(16,185,129,0.8)]"></span>
                                    Hoạt động
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-red-500/20 text-red-400 border border-red-500/30 shadow-[0_0_10px_rgba(239,68,68,0.2)]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400 mr-1.5 shadow-[0_0_5px_rgba(239,68,68,0.8)]"></span>
                                    Tạm ẩn
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-bl/60">
                            {{ optional($post->created_at)->format('d/m/Y') }}
                        </td>

                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.posts.edit', $post->id) }}"
                                   class="text-blue-400 hover:text-blue-300 font-medium transition-colors">
                                    Sửa
                                </a>

                                <form method="POST"
                                      action="{{ route('admin.posts.destroy', $post->id) }}"
                                      class="inline"
                                      onsubmit="return confirm('Bạn có chắc muốn xóa bài viết này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300 font-medium transition-colors">
                                        Xóa
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-bl/40 italic">
                            Chưa có bài viết nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($posts->hasPages())
            <div class="px-6 py-4 border-t border-white/10 bg-white/5">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
