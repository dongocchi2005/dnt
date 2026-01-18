<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingAttachment extends Model
{
    protected $fillable = [
        'booking_id',
        'path',
        'original_name',
        'mime',
        'size',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}

