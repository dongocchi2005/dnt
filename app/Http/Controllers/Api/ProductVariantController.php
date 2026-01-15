<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductVariantResolver;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    // GET /api/products/{product}/variants
    public function index(Product $product)
    {
        $variants = $product->variants()->get();
        return response()->json(['ok'=>true,'variants'=>$variants]);
    }

    // GET /api/products/{product}/variant?options[name]=value
    public function match(Request $request, Product $product)
    {
        $options = $request->query('options', []);
        if (!is_array($options)) {
            $options = [];
        }

        $resolver = new ProductVariantResolver();
        $variant = $resolver->resolveVariant($product, $options);

        return response()->json([
            'variant_id' => $variant?->id,
            'price' => $variant?->effective_price,
            'stock' => (int)($variant?->stock ?? 0),
            'sku' => $variant?->sku,
        ]);
    }
}
