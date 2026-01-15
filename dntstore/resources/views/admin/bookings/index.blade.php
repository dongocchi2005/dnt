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
                <form method="GET" action="" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
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
        <div class="overflow-x-auto">
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

        <div class="px-6 py-4 border-t border-white/10 bg-white/5">
            {{ $bookings->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
