<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $casts = [
        'booking_date' => 'datetime',
        'price' => 'decimal:2',
    ];

    protected $appends = [
        'status_key',
        'status_label',
    ];

    protected $fillable = [
        'user_id',
        'service_id',
        'customer_name',
        'phone',
        'device_name',
        'device_issue',
        'booking_date',
        'time_slot',
        'appointment_at',
        'receive_method',
        'status',
        'price',
        'notes',
        'shipping_provider',
        'pickup_address',
    ];

    public function getStatusKeyAttribute(): string
    {
        $raw = $this->attributes['status'] ?? null;
        $normalized = is_string($raw) ? mb_strtolower(trim($raw)) : null;

        $map = [
            'pending' => 'pending',
            'confirmed' => 'confirmed',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            'đang chờ' => 'pending',
            'đã xác nhận' => 'confirmed',
            'đã hoàn thành' => 'completed',
            'đã hủy' => 'cancelled',
        ];

        if (!$normalized) {
            return 'pending';
        }

        return $map[$normalized] ?? $normalized;
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending' => 'Đang chờ',
            'confirmed' => 'Đang sửa chữa',
            'completed' => 'Đã hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        return $labels[$this->status_key] ?? ($this->attributes['status'] ?? 'Đang chờ');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
