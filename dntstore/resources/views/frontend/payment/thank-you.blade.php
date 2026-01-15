@extends('frontend.layouts.app')

@section('title', 'Cảm ơn | DNT Store')

@section('content')
<div class="min-h-screen py-20">
    <div class="max-w-2xl mx-auto px-4">

        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold text-bl mb-4 neon">
                Cảm ơn bạn đã gửi ảnh thanh toán
            </h1>
            <div class="text-6xl mb-4">✅</div>
            <p class="text-bl/60 text-lg">
                Chúng tôi đã nhận được ảnh chuyển khoản của bạn
            </p>
        </div>

        {{-- Card --}}
        <div class="cyber-panel cyber-corners p-8 text-center">

            <div class="space-y-6">
                <div>
                    <h3 class="text-xl font-bold text-bl mb-2">Đơn hàng của bạn đang được xử lý</h3>
                    <p class="text-bl/60">
                        Shop sẽ xác nhận thanh toán trong vòng 24 giờ làm việc.
                        Bạn sẽ nhận được thông báo qua email khi đơn hàng được xác nhận.
                    </p>
                </div>

                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                    <div class="text-sm text-bl/60 mb-2">Mã đơn hàng:</div>
                    <div class="text-lg font-semibold text-cyan-400">
                        @if(isset($order))
                            #{{ $order->id }}
                        @else
                            #{{ $booking->id }}
                        @endif
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @if(isset($order))
                        <a href="{{ route('home') }}"
                           class="inline-block px-6 py-3 bg-white/10 hover:bg-white/20 text-bl font-semibold rounded-lg transition">
                            ← Tiếp tục mua sắm
                        </a>
                        <a href="{{ route('booking.history') }}"
                           class="inline-block px-6 py-3 bg-cyan-500 hover:bg-cyan-600 text-white font-semibold rounded-lg transition">
                            Xem đơn hàng của tôi →
                        </a>
                    @else
                        <a href="{{ route('booking.history') }}"
                           class="inline-block px-6 py-3 bg-cyan-500 hover:bg-cyan-600 text-white font-semibold rounded-lg transition">
                            Xem lịch đặt của tôi →
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Additional Info --}}
        <div class="mt-8 text-center">
            <div class="text-sm text-bl/60">
                <p class="mb-2">Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi:</p>
                <p>Email: support@dntstore.com | Hotline: 1900-xxxx</p>
            </div>
        </div>

    </div>
</div>
@endsection
