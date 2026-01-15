<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductVariantResolver;
use Illuminate\Http\Request;

class ClearanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('is_active', true)
            ->with('variants');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category filter: hỗ trợ lọc theo products.category hoặc products.category_id hoặc tên từ bảng categories
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where(function($q) use ($request) {
                $q->where('category', $request->category)
                  ->orWhere('category_id', $request->category)
                  ->orWhereHas('category', function($qq) use ($request) {
                      $qq->where('name', $request->category);
                  });
            });
        }

        // Price range filter: áp dụng trên giá hiển thị (min giá biến thể nếu có)
        $priceExpr = "(CASE WHEN EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = products.id) " .
                     "THEN (SELECT MIN(COALESCE(pv.sale_price, pv.original_price)) FROM product_variants pv WHERE pv.product_id = products.id) " .
                     "ELSE COALESCE(products.sale_price, products.original_price) END)";
        if ($request->filled('min_price')) {
            $query->whereRaw("$priceExpr >= ?", [$request->min_price]);
        }
        if ($request->filled('max_price')) {
            $query->whereRaw("$priceExpr <= ?", [$request->max_price]);
        }

        // Stock filter
        if ($request->filled('in_stock')) {
            $query->where('stock', '>', 0);
        }

        // Sorting
        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'price_low':
                $query->orderByRaw("$priceExpr ASC");
                break;
            case 'price_high':
                $query->orderByRaw("$priceExpr DESC");
                break;
            case 'discount':
                $query->orderByRaw('(original_price - sale_price) / original_price * 100 desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(12)->withQueryString();

        // Get available categories for filter: DISTINCT từ products.category hoặc products.category_id, fallback sang Category.name
        $categories = \App\Models\Category::whereHas('products', function($q) {
                $q->where('is_active', true);
            })
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($c) => ['value' => (string)$c->id, 'label' => $c->name]);

        $legacyCategories = Product::where('is_active', true)
            ->whereNull('category_id')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter()
            ->map(fn($name) => ['value' => (string)$name, 'label' => $name]);

        $categories = $categories->concat($legacyCategories)->values();

        // Get price range cho filter dựa trên giá hiển thị
        $priceRange = Product::where('is_active', true)
            ->selectRaw("MIN($priceExpr) as min_price, MAX($priceExpr) as max_price")
            ->first();

        return view('frontend.clearance.index', compact('products', 'categories', 'priceRange'));
    }

   public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'images' => fn($q) => $q->orderBy('sort_order')->orderBy('id'),
                'productImages', 
                'variants',
                'variants.values',
                'reviews.user'
            ])
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

        $canReview = false;
        if (auth()->check()) {
            $userId = auth()->id();
            $canReview = \App\Models\Order::where('user_id', $userId)
                ->whereHas('items', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->where(function ($q) {
                    $q->whereNotNull('delivered_at')
                      ->orWhere('order_status', 'delivered');
                })
                ->exists();
        }

        return view('frontend.clearance.show', compact('product', 'variantOptions', 'variantsPayload', 'initialVariant', 'canReview'));
    }

}
