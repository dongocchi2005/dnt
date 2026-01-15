@extends('layouts.admin')

@section('page-title','Tạo danh mục')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8 text-bl">

    <div class="mb-8">
        <h1 class="text-3xl font-bold">Tạo danh mục mới</h1>
        <p class="text-gray-300 mt-2">Thêm danh mục sản phẩm mới vào hệ thống</p>
    </div>

    <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-6">
        @csrf

        {{-- Tên danh mục --}}
        <div>
            <label for="name" class="block text-sm font-medium text-bl/80">Tên danh mục</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}"
                   class="mt-1 w-full bg-transparent border border-white/30 px-3 py-2 rounded
                          text-bl placeholder-white/50
                          focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Nhập tên danh mục"
                   required>
            @error('name')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Mô tả --}}
        <div>
            <label for="description" class="block text-sm font-medium text-bl/80">Mô tả</label>
            <textarea name="description" id="description" rows="4"
                      class="mt-1 w-full bg-transparent border border-white/30 px-3 py-2 rounded
                             text-bl placeholder-white/50
                             focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Nhập mô tả cho danh mục">{{ old('description') }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Buttons --}}
        <div class="flex items-center justify-between pt-4">
            <a href="{{ route('admin.categories.index') }}"
               class="bg-gray-600 hover:bg-gray-700 text-bl px-6 py-2 rounded-md transition-colors">
                Hủy
            </a>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-bl px-6 py-2 rounded-md transition-colors">
                Tạo danh mục
            </button>
        </div>
    </form>
</div>
@endsection
