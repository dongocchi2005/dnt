@extends('frontend.layouts.app')

@section('title', 'Thanh toán | DNT Store')

@section('content')
<div class="min-h-screen py-20">
    <div class="max-w-2xl mx-auto px-4">

        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold text-bl mb-4 neon">
                @if(isset($order) && $order->payment_method === 'cash_on_delivery')
                    Đặt hàng thành công
                @else
                    Thanh toán <span class="text-cyan-300">{{ isset($order) ? 'đơn hàng' : 'đặt lịch' }}</span>
                @endif
            </h1>
            <p class="text-bl/60 text-lg">
                @if(isset($order) && $order->payment_method === 'cash_on_delivery')
                    Đơn hàng #{{ $order->id }} đã được tạo. Bạn sẽ thanh toán khi nhận hàng.
                @elseif(isset($order))
                    Quét mã VietQR để thanh toán đơn hàng #{{ $order->id }}
                @else
                    Quét mã VietQR để thanh toán đơn đặt lịch #{{ $booking->id }}
                @endif
            </p>
        </div>

        {{-- Card --}}
        <div class="cyber-panel cyber-corners p-8">

            {{-- Order/Booking Info --}}
            <div class="mb-8">
                <h3 class="text-xl font-bold text-bl mb-4">Thông tin đơn hàng</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-bl/60">Mã đơn:</span>
                        <span class="text-bl font-semibold">
                            @if(isset($order))
                                #{{ $order->id }}
                            @else
                                #{{ $booking->id }}
                            @endif
                        </span>
                    </div>
                    @if(isset($order))
                        <div class="flex justify-between">
                            <span class="text-bl/60">Sản phẩm:</span>
                            <span class="text-bl font-semibold">
                                {{ $order->items ? $order->items->count() : 0 }} sản phẩm
                            </span>
                        </div>
                    @else
                        <div class="flex justify-between">
                            <span class="text-bl/60">Dịch vụ:</span>
                            <span class="text-bl font-semibold">
                                {{ $booking->service->name ?? 'Không xác định' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-bl/60">Ngày hẹn:</span>
                            <span class="text-bl font-semibold">
                                {{ optional($booking->booking_date)->format('d/m/Y') ?? 'Chưa xác định' }}
                            </span>
                        </div>
                    @endif
                    <div class="flex justify-between text-lg font-bold">
                        <span class="text-bl">Tổng tiền:</span>
                        <span class="text-cyan-400">
                            @if(isset($order))
                                {{ number_format($order->total_amount, 0, ',', '.') }} VND
                            @else
                                {{ number_format($booking->price, 0, ',', '.') }} VND
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- Payment Content --}}
            @if(isset($order) && $order->payment_method === 'cash_on_delivery')
                {{-- Cash on Delivery Content --}}
                <div class="text-center">
                    <div class="text-6xl mb-4">📦</div>
                    <h3 class="text-xl font-bold text-bl mb-4">Thanh toán khi nhận hàng</h3>
                    <p class="text-bl/60 mb-4">
                        Đơn hàng của bạn sẽ được giao đến địa chỉ đã cung cấp. Bạn sẽ thanh toán bằng tiền mặt khi nhận hàng.
                    </p>
                    <div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4 mb-4">
                        <div class="text-green-400 font-semibold">✓ Đơn hàng đã được xác nhận</div>
                        <div class="text-green-400/80 text-sm">Chúng tôi sẽ liên hệ với bạn để sắp xếp giao hàng.</div>
                    </div>
                </div>
            @else
                {{-- Online Payment Content --}}
                <div class="text-center">
                    <h3 class="text-xl font-bold text-bl mb-4">
                        Quét QR để thanh toán
                    </h3>

                    <div class=" bg-white p-4 rounded-xl inline-block mb-4">
                        <img
                            src="https://img.vietqr.io/image/970423-68686878899-compact.png
                            ?amount={{ isset($order) ? (int)$order->total_amount : (int)$booking->price }}
                            &addInfo={{ isset($order) ? 'ORDER_' . $order->id : 'BOOKING_' . $booking->id }}"
                            alt="QR VietQR TPBank"
                            class="w-64 h-64 mx-auto"
                        >
                    </div>

                    {{-- Payment Proof Upload Form --}}
                    <div class="mt-8 p-6 bg-white/5 rounded-xl border border-white/10">
                        <h3 class="text-xl font-bold text-bl mb-4">Tải lên ảnh thanh toán</h3>

                        @if(session('success'))
                            <div class="mb-4 p-3 bg-green-500/20 border border-green-500/50 rounded-lg text-green-400">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-4 p-3 bg-red-500/20 border border-red-500/50 rounded-lg text-red-400">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form action="{{ route('payment.uploadProof') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf

                            <input type="hidden" name="type" value="{{ isset($order) ? 'order' : 'booking' }}">
                            <input type="hidden" name="id" value="{{ isset($order) ? $order->id : $booking->id }}">

                            <div>
                                <label for="payment_proof" class="block text-sm font-medium text-bl/80 mb-2">
                                    Ảnh chuyển khoản <span class="text-red-400">*</span>
                                </label>
                                <input
                                    type="file"
                                    id="payment_proof"
                                    name="payment_proof"
                                    accept="image/*"
                                    required
                                    class="block w-full text-sm text-bl/60 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-cyan-500 file:text-white hover:file:bg-cyan-600"
                                />
                                <p class="mt-2 text-sm text-bl/60">
                                    Vui lòng tải lên ảnh chụp màn hình hoặc ảnh hóa đơn chuyển khoản. Dung lượng tối đa 2MB.
                                </p>
                            </div>

                            <button type="submit"
                                class="w-full mt-3 px-6 py-3 bg-cyan-500 hover:bg-cyan-600 text-white font-semibold rounded-lg transition">
                                Tải lên thanh toán
                            </button>

                        </form>

                        <p class="mt-4 text-sm text-bl/60 text-center">
                            Sau khi tải lên, admin sẽ kiểm tra và xác nhận thanh toán trong vòng 24 giờ.
                        </p>
                    </div>

                    <div class="text-bl/80 text-sm">
                        Nội dung chuyển khoản:
                        <div class="mt-1 text-cyan-400 font-semibold">
                            {{ isset($order) ? 'ORDER_' . $order->id : 'BOOKING_' . $booking->id }}
                        </div>
                    </div>

                    <p class="text-bl/60 text-sm mt-3">
                        Sau khi thanh toán, vui lòng chờ admin xác nhận.
                    </p>
                </div>
            @endif
        </div>

        {{-- Back --}}
        <div class="text-center mt-8">
            @if(isset($order))
                <a href="{{ route('home') }}"
                   class="inline-block px-6 py-3 bg-white/10 hover:bg-white/20 text-bl font-semibold rounded-lg transition">
                    ← Quay lại trang chủ
                </a>
            @else
                <a href="{{ route('booking.history') }}"
                   class="inline-block px-6 py-3 bg-white/10 hover:bg-white/20 text-bl font-semibold rounded-lg transition">
                    ← Quay lại lịch sử đặt lịch
                </a>
            @endif
        </div>

    </div>
</div>
@endsection
