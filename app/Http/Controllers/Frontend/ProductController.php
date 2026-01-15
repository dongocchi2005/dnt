<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductVariantResolver;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::with([
                'images' => fn($q) => $q->orderBy('sort_order')->orderBy('id'),
                'productImages', // Keep for compatibility if used elsewhere
                'variants',
                'variants.values',
            ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $resolver = new ProductVariantResolver();
        $variantOptions = $resolver->optionGroups($product);

        $variantsPayload = $product->variants()
            ->where('is_active', true)
            ->with('values')
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'price' => $v->price !== null ? (float)$v->price : null,
                    'sale_price' => $v->sale_price !== null ? (float)$v->sale_price : null,
                    'stock' => (int)($v->stock ?? 0),
                    'sku' => $v->sku,
                    'values' => $v->values
                        ->map(fn($vv) => ['name' => (string)$vv->name, 'value' => (string)$vv->value])
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

        $initialVariant = $product->variants()
            ->where('is_active', true)
            ->orderByDesc('stock')
            ->orderBy('id')
            ->first();

        return view('frontend.products.show', compact('product', 'variantOptions', 'variantsPayload', 'initialVariant'));
    }

    public function getVariant(Request $request, Product $product)
    {
        $options = $request->query('options', []);
        if (!is_array($options)) {
            $options = [];
        }

        $resolver = new ProductVariantResolver();
        $variant = $resolver->resolveVariant($product, $options);

        return response()->json($variant ? $resolver->variantPayload($variant) : [
            'variant_id' => null,
            'price' => null,
            'stock' => 0,
            'sku' => null,
        ]);
    }
}
