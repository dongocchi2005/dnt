<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'variant_name',
        'sku',
        'price',
        'original_price',
        'sale_price',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function values()
    {
        return $this->hasMany(ProductVariantValue::class, 'product_variant_id');
    }

    public function getEffectivePriceAttribute(): ?float
    {
        $p = $this->sale_price ?? $this->price ?? $this->original_price;
        return $p !== null ? (float)$p : null;
    }
}
