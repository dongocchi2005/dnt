<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminServiceOrderQuoteRequest;
use App\Http\Requests\AdminServiceOrderShipmentRequest;
use App\Models\ServiceOrder;
use App\Models\ServiceShipment;
use App\Services\ServiceOrderWorkflow;
use Illuminate\Http\Request;

class ServiceOrderController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        $status = $request->query('status');
        $isFullyPaid = $request->query('is_fully_paid');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $totalMin = $request->query('total_min');
        $totalMax = $request->query('total_max');

        $orders = ServiceOrder::query()
            ->when($q !== '', function ($qq) use ($q) {
                $like = '%' . $q . '%';
                $qq->where(function ($sub) use ($like) {
                    $sub->where('code', 'like', $like)
                        ->orWhere('customer_name', 'like', $like)
                        ->orWhere('customer_phone', 'like', $like);
                });
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($isFullyPaid !== null && $isFullyPaid !== '', fn($qq) => $qq->where('is_fully_paid', (bool)$isFullyPaid))
            ->when($dateFrom, fn($qq) => $qq->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($qq) => $qq->whereDate('created_at', '<=', $dateTo))
            ->when(is_numeric($totalMin), fn($qq) => $qq->where('total_amount', '>=', (float)$totalMin))
            ->when(is_numeric($totalMax), fn($qq) => $qq->where('total_amount', '<=', (float)$totalMax))
            ->latest()
            ->paginate(15)
            ->appends($request->query());

        $statuses = ServiceOrderWorkflow::STATUSES;

        return view('admin.service-orders.index', compact('orders', 'status', 'statuses'));
    }

    public function show(ServiceOrder $serviceOrder)
    {
        $serviceOrder->load(['device', 'payments', 'shipments', 'statusHistories.user']);

        return view('admin.service-orders.show', compact('serviceOrder'));
    }

    public function markReceived(ServiceOrder $serviceOrder, ServiceOrderWorkflow $workflow)
    {
        $workflow->transition($serviceOrder, 'received', ['note' => 'Mark received']);

        return back()->with('success', 'Đã cập nhật trạng thái nhận máy.');
    }

    public function setQuoted(AdminServiceOrderQuoteRequest $request, ServiceOrder $serviceOrder, ServiceOrderWorkflow $workflow)
    {
        $serviceOrder->fill($request->only(['inspection_fee', 'repair_fee', 'shipping_fee', 'notes_admin']));
        $serviceOrder->recalculateTotals();
        $serviceOrder->save();

        if ($serviceOrder->status === 'received') {
            $workflow->transition($serviceOrder, 'diagnosing', ['note' => 'Auto diagnosing before quote']);
        }

        $workflow->transition($serviceOrder, 'quoted', ['note' => 'Set quoted']);

        return back()->with('success', 'Đã báo giá.');
    }

    public function markInRepair(ServiceOrder $serviceOrder, ServiceOrderWorkflow $workflow)
    {
        $workflow->transition($serviceOrder, 'in_repair', ['note' => 'Mark in repair']);

        return back()->with('success', 'Đã chuyển sang đang sửa.');
    }

    public function markReadyToReturn(ServiceOrder $serviceOrder, ServiceOrderWorkflow $workflow)
    {
        $workflow->transition($serviceOrder, 'ready_to_return', ['note' => 'Mark ready to return']);

        return back()->with('success', 'Đã sẵn sàng trả máy.');
    }

    public function markCompleted(ServiceOrder $serviceOrder, ServiceOrderWorkflow $workflow)
    {
        $workflow->transition($serviceOrder, 'completed', ['note' => 'Mark completed']);

        return back()->with('success', 'Đã hoàn tất đơn.');
    }

    public function createOutboundShipment(
        AdminServiceOrderShipmentRequest $request,
        ServiceOrder $serviceOrder,
        ServiceOrderWorkflow $workflow
    ) {
        if ($serviceOrder->status !== 'ready_to_return') {
            return back()->withErrors(['status' => 'Đơn chưa sẵn sàng trả.']);
        }

        if ($serviceOrder->return_method !== 'ship') {
            return back()->withErrors(['return_method' => 'Đơn không chọn trả bằng ship.']);
        }

        $shippingFee = (float)$serviceOrder->shipping_fee;
        $codAmount = (float)$request->input('cod_amount', 0);
        $paidShipping = $serviceOrder->payments()
            ->where('type', 'shipping')
            ->where('status', 'paid')
            ->sum('amount');

        if ($shippingFee > 0 && $paidShipping < $shippingFee && $codAmount < $shippingFee) {
            return back()->withErrors(['shipping_fee' => 'Cần thu phí ship hoặc COD trước khi tạo vận đơn.']);
        }

        ServiceShipment::create([
            'service_order_id' => $serviceOrder->id,
            'direction' => 'outbound',
            'carrier' => $request->input('carrier'),
            'tracking_code' => $request->input('tracking_code'),
            'label_url' => $request->input('label_url'),
            'fee' => (float)$request->input('fee', $shippingFee),
            'cod_amount' => $codAmount,
            'status' => 'created',
        ]);

        $workflow->transition($serviceOrder, 'return_shipping', ['note' => 'Create outbound shipment']);

        return back()->with('success', 'Đã tạo vận đơn trả về.');
    }
}
