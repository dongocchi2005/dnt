@extends('layouts.admin')

@section('title', 'Chi tiết đơn hàng')

@section('content')
<div class="container mx-auto px-4 py-8 text-bl">

    <a href="{{ route('admin.orders.index') }}"
       class="text-sm text-bl/80 hover:text-bl">
        ← Trở về danh sách
    </a>

    <div class="mt-4">
        <h2 class="text-xl font-bold mb-4 text-bl">
            Đơn hàng #{{ $order->id }}
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Cột trái --}}
            <div>
                <h3 class="font-semibold text-bl/90">Khách hàng</h3>
                <p class="text-bl">{{ $order->user->name ?? 'Khách vãng lai' }}</p>
                <p class="text-sm text-bl/70">{{ $order->user->email ?? '' }}</p>

                <h3 class="font-semibold mt-4 text-bl/90">Sản phẩm</h3>
                @foreach($order->items as $item)
                    <div class="mb-3 p-3 rounded-xl border border-white/15 bg-white/5">
                        <p class="text-bl font-medium">{{ $item->product_name }}</p>
                        <p class="text-sm text-bl/70">Số lượng: {{ $item->quantity }} x {{ number_format($item->price) }} VND</p>
                        <p class="text-sm text-bl/70">Tổng: {{ number_format($item->subtotal) }} VND</p>
                    </div>
                @endforeach
            </div>

            {{-- Cột phải --}}
            <div>
                <h3 class="font-semibold text-bl/90">Chi tiết</h3>

                <p class="text-bl">Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}</p>
                <p class="text-bl">Tổng tiền: {{ number_format($order->total_amount) }} VND</p>

                <p class="text-bl">Phương thức thanh toán:
                    @if($order->payment_method === 'cash_on_delivery')
                        <span class="px-2 py-1 rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30 text-xs">
                            Thanh toán khi nhận hàng
                        </span>
                    @else
                        <span class="px-2 py-1 rounded-full bg-purple-500/20 text-purple-300 border border-purple-500/30 text-xs">
                            Thanh toán online
                        </span>
                    @endif
                </p>

                <p class="mt-4 text-bl flex items-center gap-2">
                    Trạng thái thanh toán:
                    @if($order->payment_status === 'completed')
                        <span class="px-2 py-1 rounded-full bg-green-500/20 text-green-300 border border-green-500/30 text-xs">
                            Đã thanh toán
                        </span>
                    @elseif($order->payment_status === 'failed')
                        <span class="px-2 py-1 rounded-full bg-red-500/20 text-red-300 border border-red-500/30 text-xs">
                            Thất bại
                        </span>
                    @else
                        <span class="px-2 py-1 rounded-full bg-yellow-500/20 text-yellow-300 border border-yellow-500/30 text-xs">
                            Chờ xác nhận
                        </span>
                    @endif
                </p>

                @if($order->payment_method !== 'cash_on_delivery')
                    {{-- Disable nút khi đã xử lý --}}
                    <div class="mt-4 flex gap-2">
                        <form method="POST" action="{{ route('admin.orders.payment.confirm', $order) }}">
                            @csrf
                            <button
                                type="submit"
                                @disabled(in_array($order->payment_status, ['completed','failed']))
                                class="px-4 py-2 rounded-lg font-semibold
                                       {{ in_array($order->payment_status, ['completed','failed']) ? 'bg-green-600/30 text-bl/40 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700 text-bl' }}">
                                Xác nhận thanh toán
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.orders.payment.reject', $order) }}">
                            @csrf
                            <button
                                type="submit"
                                @disabled(in_array($order->payment_status, ['completed','failed']))
                                class="px-4 py-2 rounded-lg font-semibold
                                       {{ in_array($order->payment_status, ['completed','failed']) ? 'bg-red-600/30 text-bl/40 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700 text-bl' }}">
                                Từ chối thanh toán
                            </button>
                        </form>
                    </div>

                    <p class="mt-2 text-xs text-bl/60">
                        Lưu ý: Sau khi xác nhận/từ chối, nút sẽ bị khóa để tránh xử lý lại.
                    </p>
                @else
                    {{-- COD: Vận chuyển + Đã giao & đã thu tiền --}}
                    <div class="mt-4">
                        <p class="text-bl/70 text-sm mb-2">Trạng thái giao hàng:</p>
                        <div class="flex items-center gap-4">
                            @if($order->order_status === 'delivered')
                                <span class="px-3 py-1 rounded-full bg-green-500/20 text-green-300 border border-green-500/30 text-sm">
                                    <i class="fa-solid fa-check-circle mr-1"></i>
                                    Đã giao & đã thu tiền
                                </span>
                            @elseif($order->order_status === 'shipping')
                                <span class="px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30 text-sm">
                                    <i class="fa-solid fa-truck mr-1"></i>
                                    Đang giao hàng
                                </span>
                                <form method="POST" action="{{ route('admin.orders.mark-delivered', $order) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded-lg font-semibold bg-green-600 hover:bg-green-700 text-bl text-sm">
                                        <i class="fa-solid fa-check mr-1"></i>
                                        Đã giao & đã thu tiền
                                    </button>
                                </form>
                            @else
                                <span class="px-3 py-1 rounded-full bg-yellow-500/20 text-yellow-300 border border-yellow-500/30 text-sm">
                                    Chờ xử lý
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Panel ảnh chuyển khoản giống booking --}}
    <div class="mt-6 p-4 border border-white/15 rounded-2xl bg-white/5">
        <h3 class="font-bold mb-2 text-bl">Xác nhận thanh toán</h3>

        <div class="mb-2">
            <span class="text-bl/70">Trạng thái:</span>
            @if($order->payment_status === 'completed')
                <span class="text-green-300 font-bold">Đã xác nhận</span>
            @elseif($order->payment_status === 'failed')
                <span class="text-red-300 font-bold">Từ chối</span>
            @else
                <span class="text-yellow-300 font-bold">Chờ xác nhận</span>
            @endif
        </div>

        @if($order->payment_proof)
            <div class="mt-3">
                <div class="mb-2 text-bl/70">Ảnh chuyển khoản:</div>

                <a href="{{ asset($order->payment_proof) }}" target="_blank">
                    <img
                        src="{{ asset($order->payment_proof) }}"
                        class="max-w-[520px] rounded-xl border border-white/15"
                        alt="payment proof"
                    >
                </a>
            </div>
        @else
            <div class="text-bl/50">Chưa có ảnh chuyển khoản.</div>
        @endif
    </div>

    {{-- Panel vận chuyển --}}
    <div class="mt-6 p-4 border border-white/15 rounded-2xl bg-white/5">
        <h3 class="font-bold mb-4 text-bl">Thông tin vận chuyển</h3>

        @if($order->tracking_url)
            {{-- Form đã khóa --}}
            <div class="space-y-3 opacity-60">
                <div>
                    <label class="block text-sm text-bl/70 mb-1">Đơn vị vận chuyển</label>
                    <input value="{{ $order->shipping_carrier }}"
                           class="w-full px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-bl placeholder-white/50"
                           disabled readonly>
                </div>

                <div>
                    <label class="block text-sm text-bl/70 mb-1">Mã vận đơn (nếu có)</label>
                    <input value="{{ $order->tracking_code }}"
                           class="w-full px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-bl placeholder-white/50"
                           disabled readonly>
                </div>

                <div>
                    <label class="block text-sm text-bl/70 mb-1">Link theo dõi vận chuyển</label>
                    <input value="{{ $order->tracking_url }}"
                           class="w-full px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-bl placeholder-white/50"
                           disabled readonly>
                    <p class="text-xs text-bl/50 mt-1">Khách sẽ bấm link này để xem hành trình giao hàng.</p>
                </div>

                <button type="submit" class="px-4 py-2 rounded-lg font-semibold bg-gray-600 text-bl cursor-not-allowed" disabled>
                    Lưu vận chuyển
                </button>
            </div>

            <div class="mt-4 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                <p class="text-yellow-300 text-sm">
                    <i class="fa-solid fa-lock mr-2"></i>
                    Đã khóa để tránh sửa nhầm
                </p>
            </div>

            <div class="mt-4 p-3 bg-green-500/10 border border-green-500/20 rounded-lg">
                <p class="text-green-300 text-sm">
                    <i class="fa-solid fa-check-circle mr-2"></i>
                    Đã có link theo dõi vận chuyển
                </p>
                <a href="{{ $order->tracking_url }}" target="_blank" rel="noopener"
                   class="text-blue-300 hover:underline text-sm mt-1 inline-block">
                    Xem link tracking
                </a>
            </div>
        @else
            {{-- Form có thể chỉnh sửa --}}
            <form method="POST" action="{{ route('admin.orders.tracking.update', $order->id) }}" class="space-y-3">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm text-bl/70 mb-1">Đơn vị vận chuyển</label>
                    <input name="shipping_carrier" value="{{ old('shipping_carrier', $order->shipping_carrier) }}"
                           class="w-full px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-bl placeholder-white/50"
                           placeholder="SPX / GHTK / GHN...">
                </div>

                <div>
                    <label class="block text-sm text-bl/70 mb-1">Mã vận đơn (nếu có)</label>
                    <input name="tracking_code" value="{{ old('tracking_code', $order->tracking_code) }}"
                           class="w-full px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-bl placeholder-white/50"
                           placeholder="VD: SPXVN123...">
                </div>

                <div>
                    <label class="block text-sm text-bl/70 mb-1">Link theo dõi vận chuyển</label>
                    <input name="tracking_url" value="{{ old('tracking_url', $order->tracking_url) }}"
                           class="w-full px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-bl placeholder-white/50"
                           placeholder="Dán link tracking từ Shopee/SPX">
                    <p class="text-xs text-bl/50 mt-1">Khách sẽ bấm link này để xem hành trình giao hàng.</p>
                </div>

                <button type="submit" class="px-4 py-2 rounded-lg font-semibold bg-blue-600 hover:bg-blue-700 text-bl">
                    Lưu vận chuyển
                </button>
            </form>
        @endif
    </div>

</div>
@endsection
