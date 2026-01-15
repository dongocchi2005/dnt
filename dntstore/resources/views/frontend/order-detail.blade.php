@extends('frontend.layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->id)

@section('content')
<div class="min-h-screen px-4 py-10">
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold neon">
                    Đơn hàng #{{ $order->id }}
                </h1>
                <p class="text-white/60">
                    {{ $order->created_at->format('d/m/Y H:i') }}
                </p>
            </div>

            <span class="px-4 py-1 rounded-full text-sm font-semibold
                @if(in_array($order->payment_status, ['paid','completed']))
                    bg-green-500/20 text-green-400 border border-green-500/30
                @elseif($order->payment_status === 'pending')
                    bg-yellow-500/20 text-yellow-400 border border-yellow-500/30
                @else
                    bg-red-500/20 text-red-400 border border-red-500/30
                @endif
            ">
                @switch($order->payment_status)
                    @case('completed') Đã thanh toán @break
                    @case('paid') Đã thanh toán @break
                    @case('pending') Chờ thanh toán @break
                    @default {{ $order->payment_status }}
                @endswitch
            </span>
        </div>

        <!-- Order info -->
        <div class="cyber-panel cyber-corners p-6 space-y-4">
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <p class="text-white/60 text-sm">Mã giao dịch</p>
                    <p class="font-semibold">{{ $order->transaction_id ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-white/60 text-sm">Phương thức</p>
                    <p class="font-semibold uppercase">{{ $order->payment_method }}</p>
                </div>
                <div>
                    <p class="text-white/60 text-sm">Tổng tiền</p>
                    <p class="text-2xl font-bold text-cyan-300">
                        {{ number_format($order->total_amount) }} VND
                    </p>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div class="cyber-panel p-6">
            <h2 class="text-xl font-bold mb-4 neon">Sản phẩm</h2>

            <div class="space-y-3">
                @foreach($order->items as $item)
                <div class="flex items-center gap-4 bg-white/5 rounded-lg p-3">
                    @php
                        $imgPath = $item->product_image ?? '';
                        if ($imgPath && (\Illuminate\Support\Str::startsWith($imgPath, ['http://', 'https://', '/']))) {
                            $imgUrl = $imgPath;
                        } elseif ($imgPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($imgPath)) {
                            $imgUrl = \Illuminate\Support\Facades\Storage::url($imgPath);
                        } elseif ($imgPath) {
                            $imgUrl = asset($imgPath);
                        } else {
                            $imgUrl = null;
                        }
                    @endphp
                    @if($imgUrl)
                        <img src="{{ $imgUrl }}"
                             class="w-14 h-14 object-cover rounded">
                    @else
                        <div class="w-14 h-14 bg-white/10 rounded flex items-center justify-center text-xs">
                            No img
                        </div>
                    @endif

                    <div class="flex-1">
                        <p class="font-semibold">{{ $item->product_name }}</p>
                        <p class="text-white/60 text-sm">
                            SL: {{ $item->quantity }} × {{ number_format($item->price) }} VND
                        </p>
                    </div>

                    <div class="font-bold text-cyan-300">
                        {{ number_format($item->quantity * $item->price) }} VND
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between">
            <a href="{{ route('orders.history') }}"
               class="px-4 py-2 rounded-lg
                      bg-white/10 hover:bg-white/20
                      border border-white/20 transition">
                ← Quay lại
            </a>

            <div class="flex gap-3">
                @if($order->tracking_url)
                    <a href="{{ $order->tracking_url }}"
                       target="_blank"
                       rel="noopener"
                       class="px-5 py-2 rounded-lg
                              bg-gradient-to-r from-cyan-500/30 to-purple-500/30
                              hover:from-cyan-400 hover:to-purple-500
                              text-cyan-200 hover:text-black
                              border border-cyan-400/30 transition">
                        <i class="fa-solid fa-truck mr-2"></i>
                        Xem vận chuyển
                    </a>
                @else
                    <span class="px-5 py-2 rounded-lg
                                 bg-gray-500/20 text-gray-400
                                 border border-gray-400/30">
                        Chưa có link vận chuyển
                    </span>
                @endif

                @if($order->payment_status === 'pending')
                <a href=""{{ route('payment.pay', $order->id) }}"
                   class="px-5 py-2 rounded-lg
                          bg-gradient-to-r from-cyan-500/30 to-purple-500/30
                          hover:from-cyan-400 hover:to-purple-500
                          text-cyan-200 hover:text-black
                          border border-cyan-400/30 transition">
                    Thanh toán
                </a>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
