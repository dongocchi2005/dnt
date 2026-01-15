<?php

namespace App\Services;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderStatusHistory;
use Illuminate\Support\Facades\Auth;

class ServiceOrderWorkflow
{
    public const STATUSES = [
        'pending',
        'awaiting_device',
        'received',
        'diagnosing',
        'quoted',
        'in_repair',
        'ready_to_return',
        'return_shipping',
        'completed',
        'canceled',
    ];

    private array $transitions = [
        'pending' => ['awaiting_device', 'canceled'],
        'awaiting_device' => ['received', 'canceled'],
        'received' => ['diagnosing', 'canceled'],
        'diagnosing' => ['quoted', 'canceled'],
        'quoted' => ['in_repair', 'canceled'],
        'in_repair' => ['ready_to_return', 'canceled'],
        'ready_to_return' => ['return_shipping', 'completed', 'canceled'],
        'return_shipping' => ['completed', 'canceled'],
        'completed' => [],
        'canceled' => [],
    ];

    public function canTransition(ServiceOrder $order, string $to): bool
    {
        $from = $order->status ?? 'pending';
        $allowed = $this->transitions[$from] ?? [];

        return in_array($to, $allowed, true);
    }

    public function transition(ServiceOrder $order, string $to, array $context = []): ServiceOrder
    {
        $from = $order->status ?? 'pending';
        if (!$this->canTransition($order, $to)) {
            throw new \InvalidArgumentException("Transition {$from} -> {$to} not allowed.");
        }

        $order->status = $to;
        $order->save();

        ServiceOrderStatusHistory::create([
            'service_order_id' => $order->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by' => $context['changed_by'] ?? Auth::id(),
            'note' => $context['note'] ?? null,
            'meta' => $context['meta'] ?? null,
        ]);

        return $order;
    }
}
