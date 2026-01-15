<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show($id)
    {
        $product = Product::with(['variants', 'productImages'])->findOrFail($id);
        return response()->json([
            'ok' => true,
            'product' => $product,
        ]);
    }

    public function showBySlug($slug)
    {
        $product = Product::with(['variants', 'productImages'])
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'ok' => true,
            'product' => $product,
        ]);
    }
}
