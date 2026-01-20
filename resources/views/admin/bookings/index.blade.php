@extends('layouts.admin')

@section('title', 'Quản lý đặt lịch')

@section('content')
<div class="space-y-6">
    <div class="cyber-panel p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-bl font-display neon">Quản lý đặt lịch</h1>
                <p class="text-bl/60 mt-1">Danh sách yêu cầu đặt lịch sửa chữa</p>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Tìm tên, SĐT..."
                        class="px-4 py-2 border border-white/10 bg-white/5 text-bl placeholder-white/30 rounded-lg text-sm flex-1 sm:flex-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all"
                    />

                    <select
                        name="status"
                        class="px-4 py-2 border border-white/10 bg-white/5 text-bl rounded-lg text-sm appearance-none flex-1 sm:flex-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all [&>option]:bg-gray-900"
                    >
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Đang chờ</option>
                        <option value="confirmed" {{ request('status')=='confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                        <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                        <option value="cancelled" {{ request('status')=='cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    </select>

                    <select
                        name="service_id"
                        class="px-4 py-2 border border-white/10 bg-white/5 text-bl rounded-lg text-sm appearance-none flex-1 sm:flex-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all [&>option]:bg-gray-900"
                    >
                        <option value="">Tất cả dịch vụ</option>
                        @foreach($services as $svc)
                            <option value="{{ $svc->id }}" {{ (string)request('service_id')===(string)$svc->id ? 'selected' : '' }}>
                                {{ $svc->name }}
                            </option>
                        @endforeach
                    </select>

                    <select
                        name="receive_method"
                        class="px-4 py-2 border border-white/10 bg-white/5 text-bl rounded-lg text-sm appearance-none flex-1 sm:flex-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all [&>option]:bg-gray-900"
                    >
                        <option value="">Tất cả hình thức</option>
                        <option value="store" {{ request('receive_method')==='store' ? 'selected' : '' }}>Nhận tại cửa hàng</option>
                        <option value="ship" {{ request('receive_method')==='ship' ? 'selected' : '' }}>Gửi ship</option>
                        <option value="pickup" {{ request('receive_method')==='pickup' ? 'selected' : '' }}>Đến lấy tận nơi</option>
                        <option value="shipping" {{ request('receive_method')==='shipping' ? 'selected' : '' }}>Gửi ship</option>
                    </select>

                    <input
                        type="date"
                        name="date_from"
                        value="{{ request('date_from') }}"
                        class="px-4 py-2 border border-white/10 bg-white/5 text-bl rounded-lg text-sm flex-1 sm:flex-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all"
                    />
                    <input
                        type="date"
                        name="date_to"
                        value="{{ request('date_to') }}"
                        class="px-4 py-2 border border-white/10 bg-white/5 text-bl rounded-lg text-sm flex-1 sm:flex-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all"
                    />

                    <button type="submit"
                            class="cyber-btn bg-blue-600 hover:bg-blue-500 text-white flex-1 sm:flex-none">
                        Lọc
                    </button>

                    <a href="{{ route('admin.bookings.index') }}"
                       class="px-4 py-2 border border-white/10 rounded-lg text-sm text-bl/60 hover:bg-white/5 text-center flex-1 sm:flex-none transition-colors flex items-center justify-center">
                        Xóa
                    </a>
                </form>
            </div>
        </div>
    </div>

    <div class="cyber-panel overflow-hidden">
        <div class="admin-table-mobile-hide overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider w-12">ID</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider">Khách hàng</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider">Thiết bị / Mô tả</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider">Ngày đặt</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider">Giá</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider">Doanh thu</th>
                        <th class="px-6 py-3 text-left font-bold text-bl/50 uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-3 text-right font-bold text-bl/50 uppercase tracking-wider">Hành động</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-white/5">
                    @foreach($bookings as $booking)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-6 py-4 text-bl/60 font-medium">
                                #{{ $booking->id }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-bold text-bl">
                                    {{ $booking->customer_name ?? ($booking->user->name ?? 'Khách vãng lai') }}
                                </div>
                                <div class="text-bl/40 text-xs mt-0.5 font-mono">
                                    {{ $booking->phone ?? ($booking->user->email ?? '') }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-bold text-bl/80">
                                    {{ $booking->device_name }}
                                </div>
                                <div class="text-bl/60 text-xs mt-0.5 truncate max-w-xs">
                                    {{ $booking->device_issue }}
                                </div>
                            </td>

                            <td class="px-6 py-4 text-bl/60">
                                {{ optional($booking->booking_date)->format('d/m/Y H:i')
                                    ?? optional($booking->created_at)->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-6 py-4 font-bold text-bl neon">
                                {{ $booking->price ? number_format($booking->price, 0, ',', '.') . ' đ' : '-' }}
                            </td>

                            <td class="px-6 py-4 text-bl/60">
                                @if($booking->status_key === 'completed' && $booking->price)
                                    <span class="text-emerald-400 font-medium">+{{ number_format($booking->price, 0, ',', '.') }}</span>
                                @else
                                    -
                                @endif
                            </td>

                            {{-- TRẠNG THÁI --}}
                            <td class="px-6 py-4">
                                @if($booking->status_key === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-[0_0_10px_rgba(16,185,129,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 shadow-[0_0_5px_rgba(16,185,129,0.8)]"></span>
                                        {{ $booking->status_label }}
                                    </span>
                                @elseif($booking->status_key === 'confirmed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30 shadow-[0_0_10px_rgba(59,130,246,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 mr-1.5 shadow-[0_0_5px_rgba(59,130,246,0.8)]"></span>
                                        {{ $booking->status_label }}
                                    </span>
                                @elseif($booking->status_key === 'cancelled')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-red-500/20 text-red-400 border border-red-500/30 shadow-[0_0_10px_rgba(239,68,68,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400 mr-1.5 shadow-[0_0_5px_rgba(239,68,68,0.8)]"></span>
                                        {{ $booking->status_label }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 shadow-[0_0_10px_rgba(234,179,8,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 mr-1.5 shadow-[0_0_5px_rgba(234,179,8,0.8)]"></span>
                                        {{ $booking->status_label }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.bookings.show', $booking->id) }}"
                                       class="p-2 text-blue-400 hover:bg-blue-500/10 rounded-lg transition-colors"
                                       title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <form action="{{ route('admin.bookings.destroy', $booking->id) }}"
                                          method="POST"
                                          class="inline-block"
                                          onsubmit="return confirm('Bạn có chắc muốn xóa lịch này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2 text-red-400 hover:bg-red-500/10 rounded-lg transition-colors"
                                                title="Xóa">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="admin-mobile-cards px-4 py-4">
            @foreach($bookings as $booking)
                <div class="admin-mobile-card">
                    <div class="admin-mobile-card__head">
                        <div class="admin-mobile-card__title text-bl">
                            #{{ $booking->id }} · {{ $booking->customer_name ?? ($booking->user->name ?? 'Khách vãng lai') }}
                        </div>
                        <div class="admin-mobile-card__meta">
                            {{ optional($booking->booking_date)->format('d/m/Y H:i') ?? optional($booking->created_at)->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div class="admin-mobile-card__body">
                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Liên hệ</div>
                            <div class="admin-mobile-field__value text-bl/80">{{ $booking->phone ?? ($booking->user->email ?? '-') }}</div>
                        </div>

                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Thiết bị</div>
                            <div class="admin-mobile-field__value text-bl/80">{{ $booking->device_name }}</div>
                        </div>

                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Mô tả</div>
                            <div class="admin-mobile-field__value text-bl/80">{{ $booking->device_issue ?: '-' }}</div>
                        </div>

                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Giá</div>
                            <div class="admin-mobile-field__value font-bold text-bl neon">
                                {{ $booking->price ? number_format($booking->price, 0, ',', '.') . ' đ' : '-' }}
                            </div>
                        </div>

                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Trạng thái</div>
                            <div class="admin-mobile-field__value">
                                @if($booking->status_key === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-[0_0_10px_rgba(16,185,129,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 shadow-[0_0_5px_rgba(16,185,129,0.8)]"></span>
                                        {{ $booking->status_label }}
                                    </span>
                                @elseif($booking->status_key === 'confirmed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30 shadow-[0_0_10px_rgba(59,130,246,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 mr-1.5 shadow-[0_0_5px_rgba(59,130,246,0.8)]"></span>
                                        {{ $booking->status_label }}
                                    </span>
                                @elseif($booking->status_key === 'cancelled')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-red-500/20 text-red-400 border border-red-500/30 shadow-[0_0_10px_rgba(239,68,68,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400 mr-1.5 shadow-[0_0_5px_rgba(239,68,68,0.8)]"></span>
                                        {{ $booking->status_label }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 shadow-[0_0_10px_rgba(234,179,8,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 mr-1.5 shadow-[0_0_5px_rgba(234,179,8,0.8)]"></span>
                                        {{ $booking->status_label }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="admin-mobile-actions">
                        <a href="{{ route('admin.bookings.show', $booking->id) }}"
                           class="admin-action-btn border border-white/10 rounded-lg text-sm font-medium text-blue-300 bg-white/5 hover:bg-white/10 transition-colors flex items-center justify-center gap-2 w-full">
                            <i class="fas fa-eye"></i>
                            <span class="admin-action-label">Xem</span>
                        </a>
                        <form action="{{ route('admin.bookings.destroy', $booking->id) }}"
                              method="POST"
                              class="w-full"
                              onsubmit="return confirm('Bạn có chắc muốn xóa lịch này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="admin-action-btn border border-red-500/20 rounded-lg text-sm font-medium text-red-300 bg-red-500/10 hover:bg-red-500/20 transition-colors flex items-center justify-center gap-2 w-full">
                                <i class="fas fa-trash-alt"></i>
                                <span class="admin-action-label">Xóa</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="px-6 py-4 border-t border-white/10 bg-white/5">
            {{ $bookings->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
