<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceDevice extends Model
{
    protected $fillable = [
        'service_order_id',
        'device_type',
        'brand',
        'model',
        'issue_description',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }
}
