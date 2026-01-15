<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class OrderShippedWithTracking extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Đơn hàng đang giao',
            'message' => 'Đơn hàng #' . $this->order->id . ' đang được giao. Bấm để theo dõi.',
            'url' => $this->order->tracking_url,
            'type' => 'order_shipped',
            'order_id' => $this->order->id,
        ];
    }
}
