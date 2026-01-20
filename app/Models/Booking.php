<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Booking extends Model
{
    protected $appends = [
        'status_key',
        'status_label',
    ];

    protected $fillable = [
        'user_id',
        'service_id',
        'booking_date',
        'time_slot',
        'customer_name',
        'phone',
        'device_name',
        'device_issue',
        'repair_note',
        'appointment_at',
        'receive_method',
        'status',
        'notes',
        'price',
        'payment_proof',
        'payment_status',
        'payment_method',
        'transaction_id',
        'shipping_provider',
        'pickup_address',
    ];

    protected $casts = [
        'appointment_at' => 'datetime',
        'booking_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $booking) {
            $appointmentAt = $booking->appointment_at instanceof \DateTimeInterface
                ? Carbon::instance($booking->appointment_at)
                : ($booking->appointment_at ? Carbon::parse($booking->appointment_at) : now());

            $booking->appointment_at = $appointmentAt;
            $booking->booking_date = $appointmentAt->toDateString();

            if (($booking->time_slot ?? '') === '') {
                $booking->time_slot = ($booking->receive_method ?? null) === 'ship'
                    ? 'ship'
                    : $appointmentAt->format('H:i');
            }
        });
    }

    public function getStatusKeyAttribute(): string
    {
        $raw = $this->attributes['status'] ?? null;

        $map = [
            'đang chờ' => 'pending',
            'pending' => 'pending',
            'đã xác nhận' => 'confirmed',
            'confirmed' => 'confirmed',
            'đã hoàn thành' => 'completed',
            'Đã hoàn thành' => 'completed',
            'completed' => 'completed',
            'đã hủy' => 'cancelled',
            'cancelled' => 'cancelled',
        ];

        $normalized = $map[$raw] ?? null;
        if ($normalized) {
            return $normalized;
        }

        return in_array($raw, ['pending', 'confirmed', 'completed', 'cancelled'], true)
            ? $raw
            : 'pending';
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending' => 'Đang chờ',
            'confirmed' => 'Đang Sửa Chữa',
            'completed' => 'Đã hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        return $labels[$this->status_key] ?? 'Đang chờ';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function attachments()
    {
        return $this->hasMany(BookingAttachment::class);
    }
}
