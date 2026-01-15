<?php

namespace App\Models;

use App\Services\ServiceOrderWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceOrder extends Model
{
    protected $fillable = [
        'code',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'receive_method',
        'return_method',
        'status',
        'deposit_amount',
        'inspection_fee',
        'repair_fee',
        'shipping_fee',
        'total_amount',
        'paid_amount',
        'is_fully_paid',
        'notes_customer',
        'notes_admin',
    ];

    protected $casts = [
        'deposit_amount' => 'decimal:2',
        'inspection_fee' => 'decimal:2',
        'repair_fee' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'is_fully_paid' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $order) {
            if (!$order->code) {
                $order->code = self::generateCode();
            }
        });

        static::created(function (self $order) {
            if (($order->status ?? 'pending') === 'pending') {
                app(ServiceOrderWorkflow::class)->transition($order, 'awaiting_device', [
                    'note' => 'Auto set after create',
                ]);
            }
        });
    }

    public static function generateCode(): string
    {
        return 'SO' . now()->format('ymd') . strtoupper(Str::random(6));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function device()
    {
        return $this->hasOne(ServiceDevice::class);
    }

    public function payments()
    {
        return $this->hasMany(ServicePayment::class);
    }

    public function shipments()
    {
        return $this->hasMany(ServiceShipment::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(ServiceOrderStatusHistory::class);
    }

    public function recalculateTotals(): void
    {
        $total = (float)$this->inspection_fee + (float)$this->repair_fee + (float)$this->shipping_fee;
        $paid = (float)$this->payments()->where('status', 'paid')->sum('amount');

        $this->total_amount = $total;
        $this->paid_amount = $paid;
        $this->is_fully_paid = $paid >= $total;
    }
}
