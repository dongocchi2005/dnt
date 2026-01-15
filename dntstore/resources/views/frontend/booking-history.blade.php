@extends('frontend.layouts.app')

@section('title', 'Lịch sử đặt lịch | DNT Store')

@section('content')
<div class="min-h-screen py-20">
    <div class="max-w-6xl mx-auto px-4">

        <div class="text-center mb-12">
            <h1 class="text-4xl font-extrabold mb-4 neon bh-title">
                Lịch sử <span class="bh-title-accent">đặt lịch</span>
            </h1>
            <p class="text-lg bh-sub">
                Theo dõi và quản lý các đơn đặt lịch sửa chữa của bạn
            </p>
        </div>

        @if($bookings->count() > 0)
            <div class="space-y-6">
                @foreach($bookings as $booking)
                    <div class="bh-card bg-white/10 backdrop-blur border border-white/20 rounded-xl p-6 hover:bg-white/15 transition">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div class="flex-1">

                                <div class="flex items-center gap-4 mb-3">
                                    <div class="text-2xl">
                                        @php
                                            $st = strtolower((string)($booking->status ?? 'pending'));
                                        @endphp

                                        @if(in_array($st, ['pending','đang chờ']))
                                            ⏳
                                        @elseif(in_array($st, ['confirmed','đã xác nhận']))
                                            ✅
                                        @elseif(in_array($st, ['completed','đã hoàn thành']))
                                            🎉
                                        @elseif(in_array($st, ['cancelled','đã hủy']))
                                            ❌
                                        @else
                                            📋
                                        @endif
                                    </div>

                                    <div>
                                        <h3 class="text-xl font-bold bh-card-title">
                                            Đơn đặt lịch #{{ $booking->id }}
                                        </h3>
                                        <p class="bh-card-sub">
                                            {{ $booking->service->name ?? 'Dịch vụ không xác định' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                                    <div>
                                        <span class="bh-meta-label text-bl/60">Ngày đặt:</span>
                                        <div class="text-bl font-semibold">
                                            {{ optional($booking->created_at)->format('d/m/Y H:i') }}
                                        </div>
                                    </div>

                                    <div>
                                        <span class="bh-meta-label text-bl/60">Ngày hẹn:</span>
                                        <div class="text-bl font-semibold">
                                            {{ optional($booking->booking_date)->format('d/m/Y') ?? 'Chưa xác định' }}
                                        </div>
                                    </div>

                                    <div>
                                        <span class="bh-meta-label text-bl/60">Trạng thái:</span>
                                        <div>
                                            @if(in_array($st, ['pending','đang chờ']))
                                                <span class="bh-badge bh-badge--pending bg-yellow-500/20 text-red-300 px-3 py-1 rounded-full text-xs font-semibold">
                                                    Chờ xác nhận
                                                </span>
                                            @elseif(in_array($st, ['confirmed','đã xác nhận']))
                                                <span class="bh-badge bh-badge--confirmed bg-blue-500/20 text-blue-300 px-3 py-1 rounded-full text-xs font-semibold">
                                                    Đang Sửa Chữa
                                                </span>
                                            @elseif(in_array($st, ['completed','đã hoàn thành']))
                                                <span class="bh-badge bh-badge--completed bg-green-500/20 text-green-300 px-3 py-1 rounded-full text-xs font-semibold">
                                                    Đã hoàn thành
                                                </span>
                                            @elseif(in_array($st, ['cancelled','đã hủy']))
                                                <span class="bh-badge bh-badge--cancelled bg-red-500/20 text-red-300 px-3 py-1 rounded-full text-xs font-semibold">
                                                    Đã hủy
                                                </span>
                                            @else
                                                <span class="bh-badge bh-badge--unknown bg-gray-500/20 text-gray-300 px-3 py-1 rounded-full text-xs font-semibold">
                                                    {{ $booking->status }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div>
                                        <span class="bh-pay-label">Thanh toán:</span>

                                        <div class="mt-1 flex flex-col gap-2">
                                            @if($booking->payment_status === 'completed')
                                                <span class="bh-pay-badge bh-pay-badge--completed bg-green-500/20 text-green-300 px-3 py-1 rounded-full text-xs font-semibold inline-block w-fit">
                                                    Đã thanh toán
                                                </span>

                                            @elseif($booking->payment_status === 'pending')
                                                <span class="bh-pay-badge bh-pay-badge--pending bg-orange-500/20 text-orange-300 px-3 py-1 rounded-full text-xs font-semibold inline-block w-fit">
                                                    Chờ thanh toán
                                                </span>

                                                @if($booking->price > 0)
                                                    <a href="{{ route('payment.pay', $booking->id) }}"
                                                       class="cyber-btn px-4 py-2 text-white text-sm font-semibold rounded-lg transition inline-flex items-center justify-center w-fit">
                                                        Thanh toán ngay
                                                    </a>
                                                @endif

                                            @elseif($booking->payment_status === 'failed')
                                                <span class="bh-pay-badge bh-pay-badge--failed bg-red-500/20 text-red-300 px-3 py-1 rounded-full text-xs font-semibold inline-block w-fit">
                                                    Thanh toán thất bại
                                                </span>

                                                @if($booking->price > 0)
                                                    <a href="{{ route('payment.pay', $booking->id) }}"
                                                       class="cyber-btn px-4 py-2 text-white text-sm font-semibold rounded-lg transition inline-flex items-center justify-center w-fit">
                                                        Thanh toán lại
                                                    </a>
                                                @endif

                                            @else
                                                <span class="bh-pay-badge bh-pay-badge--unknown bg-gray-500/20 text-gray-300 px-3 py-1 rounded-full text-xs font-semibold inline-block w-fit">
                                                    Chưa thanh toán
                                                </span>

                                                @if($booking->price > 0)
                                                    <a href="{{ route('payment.pay', $booking->id) }}"
                                                       class="cyber-btn px-4 py-2 text-white text-sm font-semibold rounded-lg transition inline-flex items-center justify-center w-fit">
                                                        Thanh toán ngay
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($booking->price > 0)
                                    <div class="mt-4">
                                        <div class="bh-price">
                                            <span class="bh-price-label">Giá:</span>
                                            <span class="font-semibold ">{{ number_format($booking->price, 0, ',', '.') }} VND</span>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($booking->notes))
                                    <div class="bh-notes mt-4 p-3 bg-white/5 rounded-lg">
                                        <span class="text-sm bh-notes-label">Ghi chú:</span>
                                        <p class="mt-1 bh-notes-text">{{ $booking->notes }}</p>
                                    </div>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex flex-col gap-2">
                                {{-- CHƯA CÓ route booking.show nên tạm thời để nút disabled, không crash --}}
                                <span
                                    class="cyber-btn px-4 py-2 text-white/70 text-sm font-semibold rounded-lg transition text-center cursor-default">
                                    Xem chi tiết
                                </span>

                                @if(in_array($st, ['pending','đang chờ']))
                                    <button type="button"
                                            class="cyber-btn px-4 py-2 text-white text-sm font-semibold rounded-lg transition text-center js-booking-cancel"
                                            data-booking-id="{{ $booking->id }}"
                                            >
                                        Hủy đặt lịch
                                    </button>
                                @endif
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-12">
                {{ $bookings->links() }}
            </div>

        @else
            <div class="text-center py-20">
                <div class="text-6xl mb-4">📅</div>
                <h3 class="text-2xl font-bold mb-2 bh-empty-title">Chưa có đơn đặt lịch nào</h3>
                <p class="mb-6 bh-empty-sub">
                    Bạn chưa có đơn đặt lịch sửa chữa nào. Hãy đặt lịch ngay để được phục vụ.
                </p>
                <a href="{{ route('booking.create') }}"
                   class="cyber-btn inline-block px-6 py-3 font-semibold rounded-lg transition">
                    Đặt lịch ngay
                </a>
            </div>
        @endif

    </div>
</div>

{{-- Nếu bạn đã có API/route huỷ đặt lịch thì thay URL bên dưới --}}
{{-- <script>
function cancelBooking(id) {
    if (!confirm('Bạn có chắc chắn muốn hủy đặt lịch này?')) return;

    // TODO: thay endpoint đúng của bạn
    fetch(`/booking/${id}/cancel`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json().catch(() => ({})))
    .then(data => {
        alert(data.message || 'Đã gửi yêu cầu hủy.');
        window.location.reload();
    })
    .catch(() => {
        alert('Có lỗi xảy ra. Vui lòng thử lại.');
    });
}
</script> --}}
@endsection
