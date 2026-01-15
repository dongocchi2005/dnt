@extends('frontend.layouts.app')

@section('title', 'Lịch sử đơn hàng')

@section('content')
<div class="min-h-screen py-20 oh-page">
    <div class="max-w-6xl mx-auto px-4 oh-wrap">
        <div class="text-center mb-12">
          <h1 class="text-4xl font-extrabold mb-4 neon oh-title">
            Lịch sử <span class="oh-title-accent">đơn hàng</span>
          </h1>
          <p class="text-lg oh-sub">Xem và theo dõi tất cả đơn hàng của bạn</p>
        </div>

        @if($orders->count() > 0)
            <div class="space-y-6">
                @foreach($orders as $order)
                @php
                    $ps = strtolower((string)($order->payment_status ?? 'pending'));
                    $payLabel = match ($ps) {
                        'paid' => 'Đã thanh toán',
                        'completed' => 'Đã thanh toán',
                        'pending' => 'Chờ thanh toán',
                        'failed' => 'Thanh toán thất bại',
                        default => (string)($order->payment_status ?? 'Chưa thanh toán'),
                    };
                    $payClass = match ($ps) {
                        'paid' => 'oh-badge--paid',
                        'completed' => 'oh-badge--paid',
                        'pending' => 'oh-badge--pending',
                        'failed' => 'oh-badge--failed',
                        default => 'oh-badge--unknown',
                    };
                @endphp

                <div class="oh-card rounded-2xl overflow-hidden">
                    <div class="p-6 oh-card-head">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="text-lg font-extrabold oh-card-title">Đơn hàng #{{ $order->id }}</h3>
                                <p class="text-sm oh-card-sub">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                <div class="flex items-center gap-2">
                                    <span class="oh-meta-label text-sm">Thanh toán:</span>
                                    <span class="oh-badge {{ $payClass }}">{{ $payLabel }}</span>
                                </div>
                                <div class="text-left sm:text-right">
                                    <p class="oh-meta-label text-sm">Tổng tiền</p>
                                    <p class="text-xl font-extrabold oh-total">{{ number_format($order->total_amount, 0, ',', '.') }} VND</p>
                                </div>
                                <a href="{{ route('orders.show', $order->id) }}" class="oh-action">
                                    Xem chi tiết
                                    <svg viewBox="0 0 24 24" fill="none"><path d="M5 12h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="m13 6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($order->items->take(3) as $item)
                            <div class="oh-item flex items-center gap-3 rounded-xl p-3">
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
                                         alt="{{ $item->product_name }}"
                                         class="w-12 h-12 object-cover rounded">
                                @else
                                    <div class="w-12 h-12 rounded flex items-center justify-center oh-noimg">
                                        <span class="text-xs oh-card-sub">No img</span>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold truncate oh-card-title">{{ $item->product_name }}</p>
                                    <p class="text-xs oh-card-sub">SL: {{ $item->quantity }} × {{ number_format($item->price, 0, ',', '.') }} VND</p>
                                </div>
                            </div>
                            @endforeach
                            @if($order->items->count() > 3)
                            <div class="oh-item flex items-center justify-center rounded-xl p-3">
                                <span class="oh-card-sub text-sm">+{{ $order->items->count() - 3 }} sản phẩm khác</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        @else
            <div class="oh-card rounded-2xl p-12 text-center">
                <div class="mb-4">
                    <svg class="mx-auto h-16 w-16 oh-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-extrabold oh-card-title mb-2">Chưa có đơn hàng nào</h3>
                <p class="oh-card-sub mb-6">Bạn chưa đặt đơn hàng nào. Hãy bắt đầu mua sắm!</p>
                <a href="{{ route('home') }}" class="oh-action">
                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Về trang chủ
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
