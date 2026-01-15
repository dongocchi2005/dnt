<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentStatusUpdated extends Notification
{
    use Queueable;

    protected $type;
    protected $model;
    protected $status;

    public function __construct(string $type, $model, string $status)
    {
        $this->type = $type; // booking | order
        $this->model = $model;
        $this->status = $status; // completed | failed
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Cập nhật thanh toán',
            'message' => $this->status === 'completed'
                ? 'Thanh toán của bạn đã được xác nhận'
                : 'Thanh toán của bạn đã bị từ chối',
            'type' => $this->type,
            'status' => $this->status,
            'id' => $this->model->id,
            'url' => $this->type === 'booking'
                ? route('booking.history')
                : route('orders.history'),
        ];
    }
}
