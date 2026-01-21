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

    <div class="cyber-panel">
        <div class="admin-panel-head">
            <div class="font-bold text-bl text-base">Bộ lọc</div>
            <div class="admin-panel-head__meta text-bl/60 text-sm">Tìm theo tiêu đề/slug, lọc trạng thái</div>
        </div>
        <div class="p-4">
            <form method="GET" action="{{ route('admin.posts.index') }}" class="admin-form-grid">
                <div class="admin-form-field admin-form-field--full">
                    <label class="sr-only" for="postSearch">Tìm bài viết</label>
                    <input id="postSearch" type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo tiêu đề..." class="admin-input" />
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="postActive">Trạng thái</label>
                    <select id="postActive" name="is_active" class="admin-input">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" {{ request('is_active')==='1' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="0" {{ request('is_active')==='0' ? 'selected' : '' }}>Tạm ẩn</option>
                    </select>
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="postDateFrom">Từ ngày</label>
                    <input id="postDateFrom" type="date" name="date_from" value="{{ request('date_from') }}" class="admin-input" />
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="postDateTo">Đến ngày</label>
                    <input id="postDateTo" type="date" name="date_to" value="{{ request('date_to') }}" class="admin-input" />
                </div>
                <div class="admin-form-field">
                    <div class="admin-form-actions admin-form-actions--full">
                        <button type="submit" class="cyber-btn admin-btn bg-blue-600 hover:bg-blue-500 text-white flex items-center justify-center gap-1">
                            Lọc
                        </button>
                        <a href="{{ route('admin.posts.index') }}"
                           class="admin-btn admin-btn-full py-2 border border-white/10 rounded-lg text-sm text-bl/60 text-center hover:bg-white/5">
                            Xóa
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="cyber-panel overflow-hidden">
        <div class="admin-table-mobile-hide overflow-x-auto">
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

        <div class="admin-mobile-cards px-4 py-4">
            @forelse($posts as $post)
                <div class="admin-mobile-card">
                    <div class="admin-mobile-card__head">
                        <div class="admin-mobile-card__title text-bl">
                            {{ $post->title }}
                        </div>
                        <div class="admin-mobile-card__meta">
                            {{ optional($post->created_at)->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="admin-mobile-card__body">
                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Ảnh</div>
                            <div class="admin-mobile-field__value">
                                @if($post->image)
                                    <img src="{{ asset($post->image) }}"
                                         alt="{{ $post->title }}"
                                         class="w-24 h-16 object-cover rounded border border-white/20 shadow-sm">
                                @else
                                    <div class="w-24 h-16 rounded border border-white/10 bg-white/5 flex items-center justify-center text-bl/30">
                                        <i class="fa-solid fa-image"></i>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Trạng thái</div>
                            <div class="admin-mobile-field__value">
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
                            </div>
                        </div>
                    </div>

                    <div class="admin-mobile-actions">
                        <a href="{{ route('admin.posts.edit', $post->id) }}"
                           class="admin-action-btn border border-white/10 rounded-lg text-sm font-medium text-bl/80 bg-white/5 hover:bg-white/10 hover:text-blue-400 transition-colors flex items-center justify-center gap-2 w-full">
                            <i class="fas fa-pen text-bl/40"></i>
                            <span class="admin-action-label">Sửa</span>
                        </a>
                        <form method="POST"
                              action="{{ route('admin.posts.destroy', $post->id) }}"
                              class="w-full"
                              onsubmit="return confirm('Bạn có chắc muốn xóa bài viết này?')">
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
                    <div class="text-center text-bl/40 italic">Chưa có bài viết nào.</div>
                </div>
            @endforelse
        </div>

        @if($posts->hasPages())
            <div class="px-6 py-4 border-t border-white/10 bg-white/5">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
