<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'customer_name',
        'phone',
        'device_name',
        'device_issue',
        'appointment_at',
        'receive_method',
        'status',
        'notes',
        'shipping_provider',
        'pickup_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
