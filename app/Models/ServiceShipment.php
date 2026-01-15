<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceShipment extends Model
{
    protected $fillable = [
        'service_order_id',
        'direction',
        'carrier',
        'tracking_code',
        'label_url',
        'fee',
        'cod_amount',
        'status',
        'meta',
    ];

    protected $casts = [
        'fee' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'meta' => 'array',
    ];

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }
}
