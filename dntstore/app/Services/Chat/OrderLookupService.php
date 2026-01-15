<?php

namespace App\Services\Chat;

use App\Models\Order;

class OrderLookupService
{
    public function lookup(?string $orderCode, ?string $phone): array
    {
        $orderCode = $orderCode ? trim((string)$orderCode) : null;
        $phone = $phone ? trim((string)$phone) : null;

        $query = Order::query()->with(['items', 'user']);

        if ($orderCode) {
            if (ctype_digit($orderCode)) {
                $query->where('id', (int)$orderCode);
            } else {
                $query->where('transaction_id', 'like', '%' . $orderCode . '%');
            }
        }

        if ($phone) {
            $query->whereHas('user', function ($q) use ($phone) {
                $q->where('phone', $phone);
            });
        }

        if (!$orderCode && !$phone) {
            return [];
        }

        return $query->latest()->limit(3)->get()->map(function (Order $order) {
            return [
                'id' => $order->id,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
                'total_amount' => (float)($order->total_amount ?? 0),
                'created_at' => optional($order->created_at)->format('d/m/Y H:i'),
                'tracking_code' => $order->tracking_code,
                'shipping_carrier' => $order->shipping_carrier,
                'items' => $order->items->map(function ($item) {
                    return [
                        'name' => $item->product_name,
                        'qty' => (int)($item->quantity ?? 0),
                    ];
                })->all(),
            ];
        })->all();
    }
}
