@extends('layouts.admin')

@section('title', 'Đơn sửa chữa')
@section('page-title', 'Đơn sửa chữa')

@section('content')
  <div class="cyber-panel p-6">
    <form class="flex flex-wrap items-center gap-3 mb-6" method="GET">
      <label class="text-sm text-bl/70">Trạng thái</label>
      <select name="status" class="px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl">
        <option value="">Tất cả</option>
        @foreach($statuses as $st)
          <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>
            {{ $st }}
          </option>
        @endforeach
      </select>
      <button type="submit">Lọc</button>
    </form>

    <div class="overflow-x-auto">
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
                  {{ $order->status }}
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

    <div class="mt-6">
      {{ $orders->links() }}
    </div>
  </div>
@endsection
