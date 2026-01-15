@extends('layouts.admin')

@section('title', 'Chi tiết dịch vụ')

@section('content')
<div class="container mx-auto px-4 py-8 text-bl">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-bl">Chi tiết dịch vụ: {{ $service->name }}</h1>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.services.edit', $service->id) }}"
               class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-bl rounded text-sm font-medium">
                Sửa dịch vụ
            </a>

            <a href="{{ route('admin.services.index') }}"
               class="text-sm text-bl/80 hover:text-bl">
                ← Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Thông tin dịch vụ -->
        <div class="lg:col-span-2">
            <div class="bg-gray-800/50 border border-white/10 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-bl mb-4">Thông tin dịch vụ</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-bl/70 mb-1">ID</label>
                        <p class="text-bl">{{ $service->id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bl/70 mb-1">Tên dịch vụ</label>
                        <p class="text-bl">{{ $service->name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bl/70 mb-1">Giá</label>
                        <p class="text-bl">{{ number_format($service->price, 0, ',', '.') }} VND</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bl/70 mb-1">Trạng thái</label>
                        <p class="text-bl">
                            @if($service->status === 'active')
                                <span class="px-2 py-1 rounded-full bg-green-500/20 text-green-300 border border-green-500/30 text-xs">
                                    Hoạt động
                                </span>
                            @elseif($service->status === 'inactive')
                                <span class="px-2 py-1 rounded-full bg-red-500/20 text-red-300 border border-red-500/30 text-xs">
                                    Không hoạt động
                                </span>
                            @else
                                <span class="px-2 py-1 rounded-full bg-yellow-500/20 text-yellow-300 border border-yellow-500/30 text-xs">
                                    {{ $service->status ?? 'Chưa xác định' }}
                                </span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bl/70 mb-1">Ngày tạo</label>
                        <p class="text-bl">{{ $service->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bl/70 mb-1">Cập nhật cuối</label>
                        <p class="text-bl">{{ $service->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <!-- Mô tả -->
                <div class="mt-6">
                    <h3 class="text-lg font-medium text-bl mb-2">Mô tả</h3>
                    <p class="text-bl/80">{{ $service->description }}</p>
                </div>
            </div>
        </div>

        <!-- Hành động -->
        <div>
            <div class="bg-gray-800/50 border border-white/10 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-bl mb-4">Hành động</h2>

                <div class="space-y-3">
                    <form action="{{ route('admin.services.update', $service->id) }}" method="POST" class="block">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="{{ $service->status === 'active' ? 'inactive' : 'active' }}">
                        <button type="submit"
                                class="w-full px-4 py-2 {{ $service->status === 'active' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-bl rounded text-sm font-medium">
                            {{ $service->status === 'active' ? 'Tắt dịch vụ' : 'Bật dịch vụ' }}
                        </button>
                    </form>

                    <a href="{{ route('admin.services.edit', $service->id) }}"
                       class="block w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-bl rounded text-sm font-medium text-center">
                        Chỉnh sửa
                    </a>

                    <form action="{{ route('admin.services.destroy', $service->id) }}" method="POST" class="block">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-bl rounded text-sm font-medium"
                                onclick="return confirm('Bạn có chắc muốn xóa dịch vụ này? Hành động này không thể hoàn tác.')">
                            Xóa dịch vụ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
