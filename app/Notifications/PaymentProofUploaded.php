<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentProofUploaded extends Notification
{
    use Queueable;

    protected $type;
    protected $model;

    public function __construct(string $type, $model)
    {
        $this->type = $type; // booking | order
        $this->model = $model;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Có thanh toán mới',
            'message' => $this->type === 'booking'
                ? 'Khách hàng đã upload ảnh chuyển khoản cho đặt lịch #' . $this->model->id
                : 'Khách hàng đã upload ảnh chuyển khoản cho đơn hàng #' . $this->model->id,
            'type' => $this->type,
            'id' => $this->model->id,
            'url' => $this->type === 'booking'
                ? route('admin.bookings.show', $this->model->id)
                : route('admin.orders.show', $this->model->id),
        ];
    }
}
