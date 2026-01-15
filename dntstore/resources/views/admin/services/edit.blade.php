@extends('layouts.admin')

@section('title', 'Chỉnh sửa dịch vụ')

@section('content')
<div class="container mx-auto px-4 py-8 text-bl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-bl">Chỉnh sửa dịch vụ: {{ $service->name }}</h1>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.services.show', $service->id) }}"
               class="text-sm text-bl/80 hover:text-bl">
                ← Quay lại chi tiết
            </a>
        </div>
    </div>

    <div class="max-w-2xl">
        <form action="{{ route('admin.services.update', $service->id) }}" method="POST" class="bg-gray-800/50 border border-white/10 rounded-lg p-6">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-bl/70 mb-2">Tên dịch vụ *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $service->name) }}"
                    required
                    class="w-full px-3 py-2 bg-gray-700 border border-white/30 text-bl placeholder-white/50 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Nhập tên dịch vụ"
                />
                @error('name')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-bl/70 mb-2">Mô tả *</label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    required
                    class="w-full px-3 py-2 bg-gray-700 border border-white/30 text-bl placeholder-white/50 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Nhập mô tả dịch vụ"
                >{{ old('description', $service->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="price" class="block text-sm font-medium text-bl/70 mb-2">Giá (VND) *</label>
                <input
                    type="number"
                    id="price"
                    name="price"
                    value="{{ old('price', $service->price) }}"
                    required
                    min="0"
                    class="w-full px-3 py-2 bg-gray-700 border border-white/30 text-bl placeholder-white/50 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Nhập giá dịch vụ"
                />
                @error('price')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="status" class="block text-sm font-medium text-bl/70 mb-2">Trạng thái</label>
                <select
                    id="status"
                    name="status"
                    class="w-full px-3 py-2 bg-gray-700 border border-white/30 text-bl rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="active" {{ old('status', $service->status) === 'active' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="inactive" {{ old('status', $service->status) === 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-4">
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-bl rounded text-sm font-medium">
                    Cập nhật dịch vụ
                </button>

                <a href="{{ route('admin.services.show', $service->id) }}"
                   class="px-6 py-2 border border-white/30 rounded text-sm text-bl hover:bg-white/5">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
