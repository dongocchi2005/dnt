<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total_amount',
        'payment_status',
        'payment_method',
        'transaction_id',
        'payment_proof',
        'order_status',
        'shipped_at',
        'delivered_at',
        'tracking_url',
        'tracking_code',
        'shipping_carrier',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
