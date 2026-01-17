@extends('layouts.admin')

@section('title', 'Thêm dịch vụ mới')

@section('content')
<div class="container mx-auto px-4 py-8 text-bl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-bl">Thêm dịch vụ mới</h1>

        <a href="{{ route('admin.services.index') }}"
           class="text-sm text-bl/80 hover:text-bl">
            ← Quay lại danh sách
        </a>
    </div>

    <div class="max-w-2xl">
        <form action="{{ route('admin.services.store') }}" method="POST"
              class="bg-gray-800/50 border border-white/10 rounded-lg p-6">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Tên dịch vụ *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-3 py-2 bg-gray-700 border border-white/30 rounded">
                @error('name') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Mô tả *</label>
                <textarea name="description" rows="4" required
                          class="w-full px-3 py-2 bg-gray-700 border border-white/30 rounded">{{ old('description') }}</textarea>
                @error('description') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Giá (VND) *</label>
                <input type="number" name="price" min="0" value="{{ old('price') }}" required
                       class="w-full px-3 py-2 bg-gray-700 border border-white/30 rounded">
                @error('price') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Trạng thái</label>
                <select name="status" class="w-full px-3 py-2 bg-gray-700 border border-white/30 rounded">
                    <option value="active">Hoạt động</option>
                    <option value="inactive">Không hoạt động</option>
                </select>
            </div>

            <button type="submit"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 rounded">
                Tạo dịch vụ
            </button>
        </form>
    </div>
</div>
@endsection
