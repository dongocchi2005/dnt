<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminServiceOrderPaymentRequest;
use App\Models\ServiceOrder;
use App\Models\ServicePayment;

class ServiceOrderPaymentController extends Controller
{
    public function store(AdminServiceOrderPaymentRequest $request, ServiceOrder $serviceOrder)
    {
        $status = $request->input('status', 'paid');

        ServicePayment::create([
            'service_order_id' => $serviceOrder->id,
            'type' => $request->input('type'),
            'method' => $request->input('method'),
            'amount' => (float)$request->input('amount'),
            'paid_at' => $status === 'paid' ? now() : null,
            'status' => $status,
            'meta' => [
                'source' => 'admin',
            ],
        ]);

        $serviceOrder->recalculateTotals();
        $serviceOrder->save();

        return back()->with('success', 'Đã ghi nhận thanh toán.');
    }
}
