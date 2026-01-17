@extends('layouts.admin')

@section('title', 'Quản lý dịch vụ')
@section('page-title', 'Quản lý dịch vụ')

@section('content')
<div class="space-y-6">
    <div class="cyber-panel p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-bl font-display neon">Quản lý dịch vụ</h1>
                <p class="text-bl/60 mt-1">Danh sách các dịch vụ sửa chữa và bảo dưỡng</p>
            </div>
            
            <div class="flex items-center gap-3">
      <a href="{{ route('admin.services.create') }}">

                   class="cyber-btn bg-blue-600 hover:bg-blue-500 text-white">
                    <i class="fa-solid fa-plus"></i> Thêm dịch vụ mới
                </a>
                
                <a href="{{ route('admin.dashboard') }}"
                   class="px-4 py-2 border border-white/10 rounded-lg text-sm text-bl/60 hover:bg-white/5 transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="cyber-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider w-16">ID</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider min-w-64">Tên dịch vụ</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider w-32">Giá</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider w-32">Trạng thái</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider w-32">Ngày tạo</th>
                        <th class="px-6 py-3 text-right font-bold text-bl/50 uppercase tracking-wider w-48">Hành động</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-white/5">
                    @foreach($services as $service)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 text-bl/60 font-medium">
                                {{ $service->id }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-bold text-bl text-base">
                                    {{ $service->name }}
                                </div>
                                <div class="text-bl/50 text-xs mt-1 truncate max-w-md">
                                    {{ Str::limit($service->description, 80) }}
                                </div>
                            </td>

                            <td class="px-6 py-4 font-bold text-bl neon">
                                {{ number_format($service->price, 0, ',', '.') }} VND
                            </td>

                            <td class="px-6 py-4">
                                @if($service->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-[0_0_10px_rgba(16,185,129,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 shadow-[0_0_5px_rgba(16,185,129,0.8)]"></span>
                                        Hoạt động
                                    </span>
                                @elseif($service->status === 'inactive')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-red-500/20 text-red-400 border border-red-500/30 shadow-[0_0_10px_rgba(239,68,68,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400 mr-1.5 shadow-[0_0_5px_rgba(239,68,68,0.8)]"></span>
                                        Ngừng hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 shadow-[0_0_10px_rgba(234,179,8,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 mr-1.5 shadow-[0_0_5px_rgba(234,179,8,0.8)]"></span>
                                        {{ $service->status }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-bl/60">
                                {{ $service->created_at->format('d/m/Y') }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end items-center gap-3">
                                    <a href="{{ route('admin.services.show', $service->id) }}"
                                       class="text-sm font-medium text-blue-400 hover:text-blue-300 transition-colors">
                                        Xem
                                    </a>
                                    
                                    <a href="{{ route('admin.services.edit', $service->id) }}"
                                       class="text-sm font-medium text-blue-400 hover:text-blue-300 transition-colors">
                                        Sửa
                                    </a>

                                    <form action="{{ route('admin.services.update', $service->id) }}"
                                          method="POST"
                                          class="inline-block">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="{{ $service->status === 'active' ? 'inactive' : 'active' }}">
                                        <button type="submit" 
                                                class="px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all shadow-sm
                                                {{ $service->status === 'active' 
                                                   ? 'bg-orange-500 hover:bg-orange-600 shadow-orange-500/20' 
                                                   : 'bg-emerald-500 hover:bg-emerald-600 shadow-emerald-500/20' }}">
                                            {{ $service->status === 'active' ? 'Tắt' : 'Bật' }}
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.services.destroy', $service->id) }}"
                                          method="POST"
                                          class="inline-block"
                                          onsubmit="return confirm('Bạn có chắc muốn xóa dịch vụ này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="px-3 py-1.5 rounded-lg bg-red-500 hover:bg-red-600 text-white text-xs font-bold transition-all shadow-sm shadow-red-500/20">
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($services->hasPages())
            <div class="px-6 py-4 border-t border-white/10 bg-white/5">
                {{ $services->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
