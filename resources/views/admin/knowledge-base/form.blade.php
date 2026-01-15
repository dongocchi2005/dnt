@extends('layouts.admin')

@section('title', 'Knowledge Base - DNT Store')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold neon">{{ isset($item) ? 'Cập nhật' : 'Tạo' }} Knowledge Base</h1>
        <p class="text-white/70 mt-1">Nội dung dùng cho chatbot và FAQ.</p>
    </div>

    <div class="cyber-panel cyber-corners p-6">
        <form method="POST" action="{{ isset($item) ? route('admin.knowledge-base.update', $item) : route('admin.knowledge-base.store') }}" class="space-y-4">
            @csrf
            @if(isset($item))
                @method('PUT')
            @endif

            <div>
                <label class="block text-sm text-white/70 mb-1">Tiêu đề</label>
                <input type="text" name="title" class="cyber-input w-full" value="{{ old('title', $item->title ?? '') }}" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-white/70 mb-1">Danh mục</label>
                    <input type="text" name="category" class="cyber-input w-full" value="{{ old('category', $item->category ?? 'general') }}">
                </div>
                <div>
                    <label class="block text-sm text-white/70 mb-1">Loại</label>
                    <input type="text" name="source_type" class="cyber-input w-full" value="{{ old('source_type', $item->source_type ?? 'faq') }}">
                </div>
            </div>

            <div>
                <label class="block text-sm text-white/70 mb-1">Tags (cách nhau bằng dấu phẩy)</label>
                <input type="text" name="tags" class="cyber-input w-full"
                       value="{{ old('tags', isset($item) && is_array($item->tags) ? implode(', ', $item->tags) : '') }}">
            </div>

            <div>
                <label class="block text-sm text-white/70 mb-1">Nội dung</label>
                <textarea name="content" rows="8" class="cyber-input w-full" required>{{ old('content', $item->content ?? '') }}</textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" id="kbActive" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
                <label for="kbActive" class="text-sm text-white/70">Hiển thị</label>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="cyber-btn px-5 py-2">{{ isset($item) ? 'Cập nhật' : 'Tạo mới' }}</button>
                <a href="{{ route('admin.knowledge-base.index') }}" class="cyber-btn px-5 py-2">Quay lại</a>
            </div>
        </form>
    </div>
</div>
@endsection
