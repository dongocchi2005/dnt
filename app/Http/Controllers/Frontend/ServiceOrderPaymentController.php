<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceOrderPaymentRequest;
use App\Models\ServiceOrder;
use App\Models\ServicePayment;

class ServiceOrderPaymentController extends Controller
{
    public function store(ServiceOrderPaymentRequest $request, ServiceOrder $serviceOrder)
    {
        if ($serviceOrder->user_id && $serviceOrder->user_id !== auth()->id()) {
            abort(403);
        }

        $amount = (float)$request->input('amount');
        $remaining = max(0, (float)$serviceOrder->total_amount - (float)$serviceOrder->paid_amount);
        if ($serviceOrder->total_amount > 0 && $remaining <= 0) {
            return back()->withErrors(['amount' => 'Đơn đã thanh toán đủ.']);
        }
        if ($remaining > 0 && $amount > $remaining) {
            $amount = $remaining;
        }

        $payment = ServicePayment::create([
            'service_order_id' => $serviceOrder->id,
            'type' => $request->input('type'),
            'method' => $request->input('method'),
            'amount' => $amount,
            'paid_at' => now(),
            'status' => 'paid',
            'meta' => [
                'source' => 'customer',
            ],
        ]);

        $serviceOrder->recalculateTotals();
        $serviceOrder->save();

        return back()->with('success', 'Đã ghi nhận thanh toán #' . $payment->id);
    }
}
