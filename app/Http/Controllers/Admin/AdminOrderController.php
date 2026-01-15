<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Revenue;
use Illuminate\Support\Facades\Log;
 use App\Notifications\PaymentStatusUpdated;

class AdminOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(\App\Http\Middleware\IsAdmin::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q = request('q');
        $status = request('status');

        $query = Order::with('user')
            ->when($q, fn($qq) => $qq->whereHas('user', fn($qUser) => $qUser->where('name', 'like', "%{$q}%"))
                                ->orWhere('id', 'like', "%{$q}%"))
            ->when($status, fn($qq) => $qq->where('payment_status', $status))
            ->latest();

        $orders = $query->paginate(20)->appends(request()->query());

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::with(['user', 'items.product'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

   
    public function destroy(string $id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        return redirect()->back()->with('success', 'Xóa đơn hàng thành công');
    }
    

public function confirmPayment(Request $request, Order $order)
{
    $order->update(['payment_status' => 'completed']);

    $order->user?->notify(
        new PaymentStatusUpdated('order', $order, 'completed')
    );

    return back()->with('success', 'Đã xác nhận thanh toán đơn hàng.');
}

public function rejectPayment(Request $request, Order $order)
{
    $order->update(['payment_status' => 'failed']);

    $order->user?->notify(
        new PaymentStatusUpdated('order', $order, 'failed')
    );

    return back()->with('success', 'Đã từ chối thanh toán đơn hàng.');
}

public function updateTracking(Request $request, Order $order)
{
    $data = $request->validate([
        'shipping_carrier' => ['nullable','string','max:50'],
        'tracking_code'    => ['nullable','string','max:100'],
        'tracking_url'     => ['nullable','url','max:2000'],
    ]);

    // Backend lock: nếu đã có tracking_url thì không cho sửa
    if ($order->tracking_url && !empty($data['tracking_url']) && $data['tracking_url'] !== $order->tracking_url) {
        return back()->with('error', 'Đơn đã có link vận chuyển, không thể sửa lại.');
    }

    $order->fill($data);

    // Nếu có link tracking lần đầu => set shipping status
    if (!empty($data['tracking_url']) && !$order->tracking_url) {
        $order->order_status = 'shipping';
        $order->shipped_at = now();

        // Gửi notification cho khách
        $order->user?->notify(new \App\Notifications\OrderShippedWithTracking($order));
    }

    $order->save();

    return back()->with('success', 'Đã cập nhật thông tin vận chuyển.');
}

public function markDelivered(Order $order)
{
    // Only allow for COD orders
    if ($order->payment_method !== 'cash_on_delivery') {
        return back()->with('error', 'Chỉ áp dụng cho đơn hàng thanh toán khi nhận hàng.');
    }

    $order->update([
        'order_status' => 'delivered',
        'payment_status' => 'completed',
        'delivered_at' => now(),
    ]);

    return back()->with('success', 'Đã cập nhật trạng thái giao hàng thành công.');
}


}
