<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOptionValue extends Model
{
    protected $table = 'product_option_values';

    protected $fillable = [
        'option_id',
        'value',
        'sort_order',
    ];

    public function option()
    {
        return $this->belongsTo(ProductOption::class, 'option_id');
    }
}
