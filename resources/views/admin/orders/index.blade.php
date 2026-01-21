@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')
@section('page-title', 'Đơn hàng')

@section('content')
<div class="space-y-6">
    @php
        $paymentMethodLabels = [
            'cash_on_delivery' => 'COD',
            'vietqr' => 'VietQR',
        ];
        $orderStatusLabels = [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'shipping' => 'Đang giao',
            'delivered' => 'Đã giao',
            'cancelled' => 'Đã hủy',
        ];
    @endphp
    <div class="cyber-panel">
        <div class="admin-panel-head">
            <div>
                <h1 class="text-2xl font-bold text-bl font-display neon">Quản lý đơn hàng</h1>
                <p class="text-bl/60 mt-1">Danh sách đơn hàng và trạng thái thanh toán</p>
            </div>
            <form method="GET" action="{{ route('admin.orders.index') }}" class="admin-form-grid">
                <div class="admin-form-field admin-form-field--full">
                    <label class="sr-only" for="orderSearch">Tìm khách hàng</label>
                    <input
                        id="orderSearch"
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Tìm tên khách hàng..."
                        class="admin-input"
                    />
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="orderStatus">Trạng thái đơn hàng</label>
                    <select
                        id="orderStatus"
                        name="status"
                        class="admin-input"
                    >
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                        <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>Đã thanh toán</option>
                        <option value="failed" {{ request('status')=='failed' ? 'selected' : '' }}>Thất bại</option>
                    </select>
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="orderPaymentMethod">Phương thức thanh toán</label>
                    <select
                        id="orderPaymentMethod"
                        name="payment_method"
                        class="admin-input"
                    >
                        <option value="">Tất cả phương thức</option>
                        <option value="cash_on_delivery" {{ request('payment_method')==='cash_on_delivery' ? 'selected' : '' }}>Thanh toán khi nhận hàng (COD)</option>
                        <option value="vietqr" {{ request('payment_method')==='vietqr' ? 'selected' : '' }}>Chuyển khoản (VietQR)</option>
                    </select>
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="orderOrderStatus">Trạng thái xử lý</label>
                    <select
                        id="orderOrderStatus"
                        name="order_status"
                        class="admin-input"
                    >
                        <option value="">Tất cả trạng thái xử lý</option>
                        <option value="pending" {{ request('order_status')==='pending' ? 'selected' : '' }}>{{ $orderStatusLabels['pending'] }}</option>
                        <option value="processing" {{ request('order_status')==='processing' ? 'selected' : '' }}>{{ $orderStatusLabels['processing'] }}</option>
                        <option value="shipping" {{ request('order_status')==='shipping' ? 'selected' : '' }}>{{ $orderStatusLabels['shipping'] }}</option>
                        <option value="delivered" {{ request('order_status')==='delivered' ? 'selected' : '' }}>{{ $orderStatusLabels['delivered'] }}</option>
                        <option value="cancelled" {{ request('order_status')==='cancelled' ? 'selected' : '' }}>{{ $orderStatusLabels['cancelled'] }}</option>
                    </select>
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="orderDateFrom">Từ ngày</label>
                    <input id="orderDateFrom" type="date" name="date_from" value="{{ request('date_from') }}" class="admin-input" />
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="orderDateTo">Đến ngày</label>
                    <input id="orderDateTo" type="date" name="date_to" value="{{ request('date_to') }}" class="admin-input" />
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="orderTotalMin">Tổng tiền từ</label>
                    <input id="orderTotalMin" type="number" inputmode="numeric" name="total_min" value="{{ request('total_min') }}" placeholder="Tổng từ..." class="admin-input" />
                </div>
                <div class="admin-form-field">
                    <label class="sr-only" for="orderTotalMax">Tổng tiền đến</label>
                    <input id="orderTotalMax" type="number" inputmode="numeric" name="total_max" value="{{ request('total_max') }}" placeholder="Tổng đến..." class="admin-input" />
                </div>
                <div class="admin-form-field">
                    <div class="admin-form-actions admin-form-actions--full">
                        <button type="submit"
                                class="cyber-btn admin-btn bg-blue-600 hover:bg-blue-500 text-white flex items-center justify-center gap-1">
                            Lọc
                        </button>
                        <a href="{{ route('admin.orders.index') }}"
                           class="admin-btn admin-btn-full py-2 border border-white/10 rounded-lg text-sm text-bl/60 text-center hover:bg-white/5">
                            Xóa
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="cyber-panel overflow-hidden">
        <div class="admin-panel-head">
            <div class="font-bold text-bl text-base">Danh sách</div>
            <div class="admin-panel-head__meta text-bl/60 text-sm">
                Tổng: <span class="text-blue-400 font-bold neon">{{ $orders->total() ?? $orders->count() }}</span>
            </div>
        </div>

        <div class="admin-table-wrap hidden sm:block">
            <table class="admin-table">
                <thead class="bg-white/5">
                    <tr>
                        <th class="text-left font-bold text-bl/50 uppercase tracking-wider w-12">ID</th>
                        <th class="text-left font-bold text-bl/50 uppercase tracking-wider">Khách hàng</th>
                        <th class="text-left font-bold text-bl/50 uppercase tracking-wider">Sản phẩm</th>
                        <th class="text-left font-bold text-bl/50 uppercase tracking-wider">Tổng tiền</th>
                        <th class="text-left font-bold text-bl/50 uppercase tracking-wider">Doanh thu</th>
                        <th class="text-left font-bold text-bl/50 uppercase tracking-wider">Phương thức</th>
                        <th class="text-left font-bold text-bl/50 uppercase tracking-wider">Ngày đặt</th>
                        <th class="text-left font-bold text-bl/50 uppercase tracking-wider">Trạng thái</th>
                        <th class="text-right font-bold text-bl/50 uppercase tracking-wider">Hành động</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-white/5">
                    @foreach($orders as $order)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="text-bl/60 font-medium">
                                #{{ $order->id }}
                            </td>

                            <td>
                                <div class="font-bold text-bl">
                                    {{ $order->user->name ?? 'Khách vãng lai' }}
                                </div>
                                <div class="text-bl/40 text-xs mt-0.5 font-mono">
                                    {{ $order->user->email ?? '' }}
                                </div>
                            </td>

                            <td>
                                @foreach($order->items as $item)
                                    <div class="text-bl/80 mb-1">{{ $item->product_name }} <span class="text-bl/40">(x{{ $item->quantity }})</span></div>
                                @endforeach
                            </td>

                            <td class="font-bold text-bl neon">
                                {{ number_format($order->total_amount) }} VND
                            </td>

                            <td class="text-bl/60">
                                @if($order->payment_status === 'completed')
                                    <span class="text-emerald-400 font-medium">+{{ number_format($order->total_amount) }}</span>
                                @else
                                    -
                                @endif
                            </td>

                            <td class="text-bl/80">
                                {{ $paymentMethodLabels[$order->payment_method] ?? ($order->payment_method ?? '-') }}
                            </td>

                            <td class="text-bl/60">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>

                            <td>
                                @if($order->payment_status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-[0_0_10px_rgba(16,185,129,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 shadow-[0_0_5px_rgba(16,185,129,0.8)]"></span>
                                        Đã thanh toán
                                    </span>
                                @elseif($order->payment_status === 'failed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-red-500/20 text-red-400 border border-red-500/30 shadow-[0_0_10px_rgba(239,68,68,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400 mr-1.5 shadow-[0_0_5px_rgba(239,68,68,0.8)]"></span>
                                        Thất bại
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 shadow-[0_0_10px_rgba(234,179,8,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 mr-1.5 shadow-[0_0_5px_rgba(234,179,8,0.8)]"></span>
                                        Chờ xác nhận
                                    </span>
                                @endif
                            </td>

                            <td class="text-right">
                                <a href="{{ route('admin.orders.show', $order->id) }}"
                                   class="admin-action-btn border border-white/10 rounded-lg text-sm font-medium text-bl/80 bg-white/5 hover:bg-white/10 hover:text-blue-400 transition-colors flex items-center justify-center gap-2">
                                    <i class="fas fa-eye text-bl/40"></i>
                                    <span class="admin-action-label">Xem</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="admin-mobile-cards block sm:hidden">
            @foreach($orders as $order)
                <div class="admin-mobile-card">
                    <div class="admin-mobile-card__head">
                        <div class="admin-mobile-card__title text-bl">
                            #{{ $order->id }} · {{ $order->user->name ?? 'Khách vãng lai' }}
                        </div>
                        <div class="admin-mobile-card__meta">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div class="admin-mobile-card__body">
                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Email</div>
                            <div class="admin-mobile-field__value text-bl/80">{{ $order->user->email ?? '-' }}</div>
                        </div>

                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Sản phẩm</div>
                            <div class="admin-mobile-field__value text-bl/80">
                                @foreach($order->items as $item)
                                    <div>{{ $item->product_name }} <span class="text-bl/50">(x{{ $item->quantity }})</span></div>
                                @endforeach
                            </div>
                        </div>

                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Tổng tiền</div>
                            <div class="admin-mobile-field__value font-bold text-bl neon">{{ number_format($order->total_amount) }} VND</div>
                        </div>

                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Phương thức</div>
                            <div class="admin-mobile-field__value text-bl/80">{{ $paymentMethodLabels[$order->payment_method] ?? ($order->payment_method ?? '-') }}</div>
                        </div>

                        <div class="admin-mobile-field">
                            <div class="admin-mobile-field__label">Trạng thái</div>
                            <div class="admin-mobile-field__value">
                                @if($order->payment_status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-[0_0_10px_rgba(16,185,129,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 shadow-[0_0_5px_rgba(16,185,129,0.8)]"></span>
                                        Đã thanh toán
                                    </span>
                                @elseif($order->payment_status === 'failed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-red-500/20 text-red-400 border border-red-500/30 shadow-[0_0_10px_rgba(239,68,68,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400 mr-1.5 shadow-[0_0_5px_rgba(239,68,68,0.8)]"></span>
                                        Thất bại
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 shadow-[0_0_10px_rgba(234,179,8,0.2)]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 mr-1.5 shadow-[0_0_5px_rgba(234,179,8,0.8)]"></span>
                                        Chờ xác nhận
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="admin-mobile-actions">
                        <a href="{{ route('admin.orders.show', $order->id) }}"
                           class="admin-action-btn border border-white/10 rounded-lg text-sm font-medium text-bl/80 bg-white/5 hover:bg-white/10 hover:text-blue-400 transition-colors flex items-center justify-center gap-2 w-full">
                            <i class="fas fa-eye text-bl/40"></i>
                            <span class="admin-action-label">Xem</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="admin-panel-footer">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
