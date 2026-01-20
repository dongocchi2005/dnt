@extends('layouts.admin')

@section('title', 'Đơn sửa chữa')
@section('page-title', 'Đơn sửa chữa')

@section('content')
  @php
    $serviceOrderStatusLabels = [
      'pending' => 'Chờ tiếp nhận',
      'awaiting_device' => 'Chờ nhận thiết bị',
      'received' => 'Đã nhận thiết bị',
      'diagnosing' => 'Đang kiểm tra',
      'quoted' => 'Đã báo giá',
      'in_repair' => 'Đang sửa chữa',
      'ready_to_return' => 'Sẵn sàng trả máy',
      'return_shipping' => 'Đang gửi trả',
      'completed' => 'Hoàn thành',
      'canceled' => 'Đã hủy',
    ];
  @endphp
  <div class="cyber-panel p-6">
    <form class="flex flex-wrap items-center gap-3 mb-6" method="GET">
      <label class="text-sm text-bl/70">Bộ lọc</label>
      <input type="text"
             name="q"
             value="{{ request('q') }}"
             placeholder="Tìm mã đơn / khách / SĐT..."
             class="px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl placeholder-white/30">
      <select name="status" class="px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl">
        <option value="">Tất cả</option>
        @foreach($statuses as $st)
          <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>
            {{ $serviceOrderStatusLabels[$st] ?? $st }}
          </option>
        @endforeach
      </select>
      <select name="is_fully_paid" class="px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl">
        <option value="">Thanh toán</option>
        <option value="1" {{ request('is_fully_paid')==='1' ? 'selected' : '' }}>Đã đủ</option>
        <option value="0" {{ request('is_fully_paid')==='0' ? 'selected' : '' }}>Chưa đủ</option>
      </select>
      <input type="date" name="date_from" value="{{ request('date_from') }}"
             class="px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl">
      <input type="date" name="date_to" value="{{ request('date_to') }}"
             class="px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl">
      <input type="number" inputmode="numeric" name="total_min" value="{{ request('total_min') }}"
             placeholder="Tổng từ..."
             class="px-3 py-2 w-32 rounded-lg border border-white/10 bg-white/5 text-bl placeholder-white/30">
      <input type="number" inputmode="numeric" name="total_max" value="{{ request('total_max') }}"
             placeholder="Tổng đến..."
             class="px-3 py-2 w-32 rounded-lg border border-white/10 bg-white/5 text-bl placeholder-white/30">
      <button type="submit">Lọc</button>
      <a href="{{ route('admin.service-orders.index') }}" class="px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl/80 hover:bg-white/10 transition-colors">
        Xóa
      </a>
    </form>

    <div class="admin-table-mobile-hide overflow-x-auto hidden md:block">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-bl/70">
            <th class="py-2">Mã đơn</th>
            <th class="py-2">Khách hàng</th>
            <th class="py-2">Trạng thái</th>
            <th class="py-2">Tổng tiền</th>
            <th class="py-2">Ngày tạo</th>
            <th class="py-2 text-right">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          @forelse($orders as $order)
            <tr class="border-t border-white/10">
              <td class="py-2 font-semibold text-bl">{{ $order->code }}</td>
              <td class="py-2">
                <div class="text-bl">{{ $order->customer_name }}</div>
                <div class="text-xs text-bl/60">{{ $order->customer_phone }}</div>
              </td>
              <td class="py-2">
                <span class="px-2 py-1 rounded-full text-xs border border-white/10">
                  {{ $serviceOrderStatusLabels[$order->status] ?? $order->status }}
                </span>
              </td>
              <td class="py-2 text-bl font-semibold">
                {{ number_format($order->total_amount) }} VND
              </td>
              <td class="py-2 text-bl/70">{{ $order->created_at->format('d/m/Y H:i') }}</td>
              <td class="py-2 text-right">
                <a href="{{ route('admin.service-orders.show', $order) }}" class="text-cyan-300 hover:underline">
                  Xem chi tiết
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="py-6 text-center text-bl/60">Chưa có đơn sửa chữa.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="admin-mobile-cards block md:hidden">
      @forelse($orders as $order)
        <div class="admin-mobile-card">
          <div class="admin-mobile-card__head">
            <div class="admin-mobile-card__title text-bl">
              {{ $order->code }} · {{ $order->customer_name }}
            </div>
            <div class="admin-mobile-card__meta">
              {{ $order->created_at->format('d/m/Y H:i') }}
            </div>
          </div>

          <div class="admin-mobile-card__body">
            <div class="admin-mobile-field">
              <div class="admin-mobile-field__label">SĐT</div>
              <div class="admin-mobile-field__value text-bl/80">{{ $order->customer_phone }}</div>
            </div>

            <div class="admin-mobile-field">
              <div class="admin-mobile-field__label">Trạng thái</div>
              <div class="admin-mobile-field__value">
                <span class="px-2 py-1 rounded-full text-xs border border-white/10">
                  {{ $serviceOrderStatusLabels[$order->status] ?? $order->status }}
                </span>
              </div>
            </div>

            <div class="admin-mobile-field">
              <div class="admin-mobile-field__label">Tổng tiền</div>
              <div class="admin-mobile-field__value font-bold text-bl neon">{{ number_format($order->total_amount) }} VND</div>
            </div>
          </div>

          <div class="admin-mobile-actions">
            <a href="{{ route('admin.service-orders.show', $order) }}"
               class="admin-action-btn border border-white/10 rounded-lg text-sm font-medium text-cyan-300 bg-white/5 hover:bg-white/10 transition-colors flex items-center justify-center gap-2 w-full">
              <i class="fas fa-eye"></i>
              <span class="admin-action-label">Xem chi tiết</span>
            </a>
          </div>
        </div>
      @empty
        <div class="admin-mobile-card">
          <div class="text-center text-bl/60">Chưa có đơn sửa chữa.</div>
        </div>
      @endforelse
    </div>

    <div class="mt-6">
      {{ $orders->links() }}
    </div>
  </div>
@endsection
