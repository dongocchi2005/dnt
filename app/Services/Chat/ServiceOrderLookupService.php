<?php

namespace App\Services\Chat;

use App\Models\ServiceOrder;

class ServiceOrderLookupService
{
    public function lookup(?string $code, ?string $phone): array
    {
        $code = $code ? strtoupper(trim((string)$code)) : null;
        $phone = $phone ? trim((string)$phone) : null;

        $query = ServiceOrder::query()->with('device');

        if ($code) {
            $query->where('code', $code);
        }

        if ($phone) {
            $query->where('customer_phone', $phone);
        }

        if (!$code && !$phone) {
            return [];
        }

        return $query->latest()->limit(3)->get()->map(function (ServiceOrder $order) {
            return [
                'id' => $order->id,
                'code' => $order->code,
                'status' => $order->status,
                'receive_method' => $order->receive_method,
                'return_method' => $order->return_method,
                'total_amount' => (float)($order->total_amount ?? 0),
                'paid_amount' => (float)($order->paid_amount ?? 0),
                'is_fully_paid' => (bool)($order->is_fully_paid ?? false),
                'device' => $order->device?->device_type,
                'issue' => $order->device?->issue_description,
            ];
        })->all();
    }
}
