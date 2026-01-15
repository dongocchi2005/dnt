<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceOrderStatusHistory extends Model
{
    protected $fillable = [
        'service_order_id',
        'from_status',
        'to_status',
        'changed_by',
        'note',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
