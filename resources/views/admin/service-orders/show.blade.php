@extends('layouts.admin')

@section('title', 'Chi tiết đơn sửa chữa')
@section('page-title', 'Chi tiết đơn sửa chữa')

@section('content')
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
      <div class="cyber-panel p-6">
        <div class="flex items-center justify-between mb-4">
          <div>
            <div class="text-sm text-bl/70">Mã đơn</div>
            <div class="text-lg font-semibold text-bl">{{ $serviceOrder->code }}</div>
          </div>
          <span class="px-3 py-1 rounded-full text-xs border border-white/10">{{ $serviceOrder->status }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div>
            <div class="text-bl/70">Khách hàng</div>
            <div class="text-bl font-semibold">{{ $serviceOrder->customer_name }}</div>
            <div class="text-bl/70">{{ $serviceOrder->customer_phone }}</div>
            <div class="text-bl/70">{{ $serviceOrder->customer_address }}</div>
          </div>
          <div>
            <div class="text-bl/70">Nhận máy</div>
            <div class="text-bl">{{ $serviceOrder->receive_method }}</div>
            <div class="text-bl/70">Trả máy</div>
            <div class="text-bl">{{ $serviceOrder->return_method }}</div>
          </div>
        </div>
      </div>

      <div class="cyber-panel p-6">
        <div class="text-sm text-bl/70 mb-3">Thiết bị</div>
        @if($serviceOrder->device)
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
              <div class="text-bl/70">Loại thiết bị</div>
              <div class="text-bl font-semibold">{{ $serviceOrder->device->device_type }}</div>
            </div>
            <div>
              <div class="text-bl/70">Hãng / Model</div>
              <div class="text-bl">{{ $serviceOrder->device->brand }} {{ $serviceOrder->device->model }}</div>
            </div>
            <div class="md:col-span-2">
              <div class="text-bl/70">Tình trạng</div>
              <div class="text-bl">{{ $serviceOrder->device->issue_description }}</div>
            </div>
          </div>
        @else
          <div class="text-bl/60">Chưa có thông tin thiết bị.</div>
        @endif
      </div>

      <div class="cyber-panel p-6">
        <div class="text-sm text-bl/70 mb-3">Báo giá</div>
        <form method="POST" action="{{ route('admin.service-orders.quoted', $serviceOrder) }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
          @csrf
          <div>
            <label class="text-xs text-bl/70">Phí kiểm tra</label>
            <input name="inspection_fee" class="w-full mt-1 px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl" value="{{ $serviceOrder->inspection_fee }}">
          </div>
          <div>
            <label class="text-xs text-bl/70">Phí sửa chữa</label>
            <input name="repair_fee" class="w-full mt-1 px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl" value="{{ $serviceOrder->repair_fee }}">
          </div>
          <div>
            <label class="text-xs text-bl/70">Phí vận chuyển</label>
            <input name="shipping_fee" class="w-full mt-1 px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl" value="{{ $serviceOrder->shipping_fee }}">
          </div>
          <div class="md:col-span-3">
            <label class="text-xs text-bl/70">Ghi chú admin</label>
            <textarea name="notes_admin" class="w-full mt-1 px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl">{{ $serviceOrder->notes_admin }}</textarea>
          </div>
          <div class="md:col-span-3">
            <button type="submit">Set quoted</button>
          </div>
        </form>
      </div>

      <div class="cyber-panel p-6">
        <div class="text-sm text-bl/70 mb-3">Thanh toán</div>
        <form method="POST" action="{{ route('admin.service-orders.payments.store', $serviceOrder) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
          @csrf
          <div>
            <label class="text-xs text-bl/70">Loại</label>
            <select name="type" class="w-full mt-1 px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl">
              <option value="deposit">deposit</option>
              <option value="inspection">inspection</option>
              <option value="repair">repair</option>
              <option value="shipping">shipping</option>
              <option value="final">final</option>
            </select>
          </div>
          <div>
            <label class="text-xs text-bl/70">Phương thức</label>
            <select name="method" class="w-full mt-1 px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl">
              <option value="cash">cash</option>
              <option value="vietqr">vietqr</option>
              <option value="cod">cod</option>
            </select>
          </div>
          <div>
            <label class="text-xs text-bl/70">Số tiền</label>
            <input name="amount" class="w-full mt-1 px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl">
          </div>
          <div>
            <label class="text-xs text-bl/70">Trạng thái</label>
            <select name="status" class="w-full mt-1 px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl">
              <option value="paid">paid</option>
              <option value="pending">pending</option>
              <option value="failed">failed</option>
            </select>
          </div>
          <div class="md:col-span-4">
            <button type="submit">Ghi nhận thanh toán</button>
          </div>
        </form>

        <div class="space-y-2 text-sm">
          @forelse($serviceOrder->payments as $payment)
            <div class="flex items-center justify-between border border-white/10 rounded-lg p-3">
              <div>
                <div class="text-bl font-semibold">#{{ $payment->id }} • {{ $payment->type }}</div>
                <div class="text-bl/70">{{ $payment->method }} • {{ $payment->status }} • {{ optional($payment->paid_at)->format('d/m/Y H:i') }}</div>
              </div>
              <div class="text-bl font-semibold">{{ number_format($payment->amount) }} VND</div>
            </div>
          @empty
            <div class="text-bl/60">Chưa có thanh toán.</div>
          @endforelse
        </div>
      </div>

      <div class="cyber-panel p-6">
        <div class="text-sm text-bl/70 mb-3">Vận chuyển</div>
        <form method="POST" action="{{ route('admin.service-orders.shipments.outbound', $serviceOrder) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
          @csrf
          <div>
            <label class="text-xs text-bl/70">Carrier</label>
            <select name="carrier" class="w-full mt-1 px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl">
              <option value="manual">manual</option>
              <option value="spx">spx</option>
              <option value="ghn">ghn</option>
            </select>
          </div>
          <div>
            <label class="text-xs text-bl/70">Tracking</label>
            <input name="tracking_code" class="w-full mt-1 px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl">
          </div>
          <div>
            <label class="text-xs text-bl/70">Label URL</label>
            <input name="label_url" class="w-full mt-1 px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl">
          </div>
          <div>
            <label class="text-xs text-bl/70">COD</label>
            <input name="cod_amount" class="w-full mt-1 px-3 py-2 rounded-lg border border-white/10 bg-white/5 text-bl">
          </div>
          <div class="md:col-span-4">
            <button type="submit">Tạo vận đơn trả về</button>
          </div>
        </form>

        <div class="space-y-2 text-sm">
          @forelse($serviceOrder->shipments as $shipment)
            <div class="flex items-center justify-between border border-white/10 rounded-lg p-3">
              <div>
                <div class="text-bl font-semibold">#{{ $shipment->id }} • {{ $shipment->direction }}</div>
                <div class="text-bl/70">{{ $shipment->carrier }} • {{ $shipment->status }}</div>
                <div class="text-bl/70">{{ $shipment->tracking_code }}</div>
              </div>
              <div class="text-bl font-semibold">{{ number_format($shipment->fee) }} VND</div>
            </div>
          @empty
            <div class="text-bl/60">Chưa có vận đơn.</div>
          @endforelse
        </div>
      </div>
    </div>

    <div class="space-y-6">
      <div class="cyber-panel p-6">
        <div class="text-sm text-bl/70 mb-3">Thao tác</div>
        <div class="space-y-2">
          <form method="POST" action="{{ route('admin.service-orders.received', $serviceOrder) }}">
            @csrf
            <button type="submit" class="w-full">Mark received</button>
          </form>
          <form method="POST" action="{{ route('admin.service-orders.in-repair', $serviceOrder) }}">
            @csrf
            <button type="submit" class="w-full">Mark in repair</button>
          </form>
          <form method="POST" action="{{ route('admin.service-orders.ready-to-return', $serviceOrder) }}">
            @csrf
            <button type="submit" class="w-full">Mark ready to return</button>
          </form>
          <form method="POST" action="{{ route('admin.service-orders.completed', $serviceOrder) }}">
            @csrf
            <button type="submit" class="w-full">Mark completed</button>
          </form>
        </div>
      </div>

      <div class="cyber-panel p-6">
        <div class="text-sm text-bl/70 mb-3">Lịch sử trạng thái</div>
        <div class="space-y-2 text-sm">
          @forelse($serviceOrder->statusHistories as $history)
            <div class="border border-white/10 rounded-lg p-3">
              <div class="text-bl font-semibold">{{ $history->from_status }} → {{ $history->to_status }}</div>
              <div class="text-bl/70">{{ optional($history->created_at)->format('d/m/Y H:i') }}</div>
              <div class="text-bl/70">{{ $history->note }}</div>
            </div>
          @empty
            <div class="text-bl/60">Chưa có lịch sử.</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
@endsection
