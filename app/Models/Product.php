<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use App\Models\ProductReview;

    class Product extends Model
    {
        protected $fillable = [
            'name',
            'slug',
            'description',
            'category_id',
            'original_price',
            'sale_price',
            'image',
            'stock',
            'is_active',
            'is_clearance',
        ];

        protected $casts = [
            'original_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_clearance' => 'boolean',
            'variants_json' => 'array',
        ];

        protected static function boot()
        {
            parent::boot();

            static::creating(function ($product) {
                if (empty($product->slug)) {
                    $product->slug = Str::slug($product->name);
                }
            });

            static::updating(function ($product) {
                if ($product->isDirty('name') && empty($product->slug)) {
                    $product->slug = Str::slug($product->name);
                }
            });
            
        }



        public function getDiscountPercentageAttribute()
        {
            if ($this->original_price > 0) {
                return round((($this->original_price - $this->sale_price) / $this->original_price) * 100);
            }
            return 0;
        }

        public function getImageUrlAttribute(): ?string
        {
            $path = $this->image;
            if (!$path) {
                return null;
            }

            if (Str::startsWith($path, ['http://', 'https://'])) {
                return $path;
            }

            if (Str::startsWith($path, ['/'])) {
                return $path;
            }

            if (Str::startsWith($path, ['image/', 'images/'])) {
                return asset($path);
            }

            if (Str::startsWith($path, ['storage/', '/storage'])) {
                return asset(ltrim($path, '/'));
            }

            if (Storage::disk('public')->exists($path)) {
                return Storage::url($path);
            }

            return asset($path);
        }

        public function getPriceAttribute()
        {
            return $this->sale_price > 0 ? $this->sale_price : $this->original_price;
        }

        // Hiển thị giá: ưu tiên defaultVariant, rồi activeVariant đầu tiên, cuối cùng product price
        public function getDisplayPriceAttribute()
        {
            if ($this->activeVariants->count() > 0) {
                $min = $this->activeVariants
                    ->map(fn($v) => $v->effective_price)
                    ->filter(fn($p) => $p !== null)
                    ->min();
                if ($min !== null) {
                    return $min;
                }
            }
            return $this->price;
        }

        public function getDisplayOriginalPriceAttribute()
        {
            if ($this->activeVariants->count() > 0) {
                $minBase = $this->activeVariants
                    ->map(fn($v) => $v->price !== null ? (float)$v->price : null)
                    ->filter(fn($p) => $p !== null)
                    ->min();
                if ($minBase !== null) {
                    return $minBase;
                }
            }
            return $this->original_price;
        }

        public function category()
        {
            return $this->belongsTo(Category::class);
        }

        public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id')->orderBy('sort_order');
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

        public function variants()
        {
            return $this->hasMany(ProductVariant::class);
        }

        public function activeVariants()
        {
            return $this->hasMany(ProductVariant::class)->where('is_active', true);
        }

        public function defaultVariant()
        {
            return $this->hasOne(ProductVariant::class)->where('is_default', true);
        }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    // Quan hệ nâng cao đã loại bỏ
}
