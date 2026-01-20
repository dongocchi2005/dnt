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
                <a href="{{ route('admin.services.create') }}"
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

    <div class="cyber-panel">
        <div class="admin-panel-head">
            <div class="font-bold text-bl text-base">Bộ lọc</div>
            <div class="admin-panel-head__meta text-bl/60 text-sm">Tìm theo tên/mô tả, lọc trạng thái & giá</div>
        </div>
        <div class="p-4">
            <form method="GET" action="{{ route('admin.services.index') }}" class="admin-form-grid">
                <div class="admin-form-field admin-form-field--full">
                    <label class="sr-only" for="serviceSearch">Tìm dịch vụ</label>
                    <input id="serviceSearch" type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo tên/mô tả..." class="admin-input" />
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="serviceStatus">Trạng thái</label>
                    <select id="serviceStatus" name="status" class="admin-input">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" {{ request('status')==='active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive" {{ request('status')==='inactive' ? 'selected' : '' }}>Ngừng hoạt động</option>
                    </select>
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="servicePriceMin">Giá từ</label>
                    <input id="servicePriceMin" type="number" inputmode="numeric" name="price_min" value="{{ request('price_min') }}" placeholder="Giá từ..." class="admin-input" />
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="servicePriceMax">Giá đến</label>
                    <input id="servicePriceMax" type="number" inputmode="numeric" name="price_max" value="{{ request('price_max') }}" placeholder="Giá đến..." class="admin-input" />
                </div>
                <div class="admin-form-field">
                    <div class="admin-form-actions admin-form-actions--full">
                        <button type="submit" class="cyber-btn admin-btn bg-blue-600 hover:bg-blue-500 text-white flex items-center justify-center gap-1">
                            Lọc
                        </button>
                        <a href="{{ route('admin.services.index') }}"
                           class="admin-btn admin-btn-full py-2 border border-white/10 rounded-lg text-sm text-bl/60 text-center hover:bg-white/5">
                            Xóa
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="cyber-panel overflow-hidden">
        <div class="admin-table-mobile-hide overflow-x-auto hidden md:block">
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

        <div class="admin-mobile-cards px-4 py-4 block md:hidden">
            @foreach($services as $service)
                <div class="admin-mobile-card">
                    <div class="admin-mobile-card__head">
                        <div class="admin-mobile-card__title text-bl">
                            {{ $service->name }}
                        </div>
                        <div class="admin-mobile-card__meta">
                            {{ $service->created_at->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="admin-mobile-card__body">
                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Mô tả</div>
                            <div class="admin-mobile-field__value text-bl/80">{{ $service->description ?: '-' }}</div>
                        </div>

                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Giá</div>
                            <div class="admin-mobile-field__value font-bold text-bl neon">
                                {{ number_format($service->price, 0, ',', '.') }} VND
                            </div>
                        </div>

                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Trạng thái</div>
                            <div class="admin-mobile-field__value">
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
                            </div>
                        </div>
                    </div>

                    <div class="admin-mobile-actions">
                        <a href="{{ route('admin.services.show', $service->id) }}"
                           class="admin-action-btn border border-white/10 rounded-lg text-sm font-medium text-bl/80 bg-white/5 hover:bg-white/10 hover:text-blue-400 transition-colors flex items-center justify-center gap-2 w-full">
                            <i class="fas fa-eye text-bl/40"></i>
                            <span class="admin-action-label">Xem</span>
                        </a>
                        <a href="{{ route('admin.services.edit', $service->id) }}"
                           class="admin-action-btn border border-white/10 rounded-lg text-sm font-medium text-bl/80 bg-white/5 hover:bg-white/10 hover:text-blue-400 transition-colors flex items-center justify-center gap-2 w-full">
                            <i class="fas fa-pen text-bl/40"></i>
                            <span class="admin-action-label">Sửa</span>
                        </a>
                        <form action="{{ route('admin.services.update', $service->id) }}"
                              method="POST"
                              class="w-full">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="{{ $service->status === 'active' ? 'inactive' : 'active' }}">
                            <button type="submit"
                                    class="admin-action-btn border border-white/10 rounded-lg text-sm font-medium text-bl/80 bg-white/5 hover:bg-white/10 transition-colors flex items-center justify-center gap-2 w-full">
                                <i class="fas fa-power-off text-bl/40"></i>
                                <span class="admin-action-label">{{ $service->status === 'active' ? 'Tắt' : 'Bật' }}</span>
                            </button>
                        </form>
                        <form action="{{ route('admin.services.destroy', $service->id) }}"
                              method="POST"
                              class="w-full"
                              onsubmit="return confirm('Bạn có chắc muốn xóa dịch vụ này?')">
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
            @endforeach
        </div>

        @if($services->hasPages())
            <div class="px-6 py-4 border-t border-white/10 bg-white/5">
                {{ $services->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
