<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q = trim((string)request('q', ''));
        $categoryId = request('category_id');
        $isActive = request('is_active');
        $isClearance = request('is_clearance');
        $priceMin = request('price_min');
        $priceMax = request('price_max');
        $stock = request('stock');

        $query = Product::query()
            ->with('category')
            ->when($q !== '', function ($qq) use ($q) {
                $like = '%' . $q . '%';
                $qq->where(function ($sub) use ($like) {
                    $sub->where('name', 'like', $like)
                        ->orWhere('slug', 'like', $like);
                });
            })
            ->when($categoryId, fn($qq) => $qq->where('category_id', $categoryId))
            ->when($isActive !== null && $isActive !== '', fn($qq) => $qq->where('is_active', (bool)$isActive))
            ->when($isClearance !== null && $isClearance !== '', fn($qq) => $qq->where('is_clearance', (bool)$isClearance))
            ->when(is_numeric($priceMin), fn($qq) => $qq->whereRaw('COALESCE(NULLIF(sale_price,0), original_price) >= ?', [(float)$priceMin]))
            ->when(is_numeric($priceMax), fn($qq) => $qq->whereRaw('COALESCE(NULLIF(sale_price,0), original_price) <= ?', [(float)$priceMax]))
            ->when($stock === 'in', fn($qq) => $qq->where('stock', '>', 0))
            ->when($stock === 'out', fn($qq) => $qq->where('stock', '<=', 0))
            ->latest();

        $products = $query->paginate(15)->appends(request()->query());
        $categories = Category::query()->select('id', 'name')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = \App\Models\Category::all();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\ProductStoreRequest $request)
    {
        $data = $request->validated();

        $data['stock'] = $data['stock'] ?? 0;
        $data['is_active'] = isset($data['is_active']) ? (bool)$data['is_active'] : false;
        $data['is_clearance'] = isset($data['is_clearance']) ? (bool)$data['is_clearance'] : false;
        $data['description'] = $data['description'] ?? '';

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('image'), $filename);
                $data['image'] = 'image/' . $filename;
            }
        }

        // Handle gallery uploads
        $galleryPaths = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                if ($file->isValid()) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('image'), $filename);
                    $galleryPaths[] = 'image/' . $filename;
                }
            }
        }

        $product = \Illuminate\Support\Facades\DB::transaction(function() use ($request, $data, $galleryPaths) {
            $product = Product::create($data);

            // Create gallery images
            foreach ($galleryPaths as $path) {
                \App\Models\ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                    'sort_order' => 0,
                ]);
            }

            $variantsInput = (array)$request->input('variants', []);
            $defaultsMarked = false;
            $items = [];
            foreach ($variantsInput as $row) {
                $combo = (string)($row['combo'] ?? '');
                $parts = array_filter(explode('|', $combo));
                $pairs = [];
                foreach ($parts as $p) {
                    [$on, $vv] = array_pad(explode(':', $p, 2), 2, null);
                    $on = trim((string)$on); $vv = trim((string)$vv);
                    if ($on !== '' && $vv !== '') $pairs[] = ['name'=>$on,'value'=>$vv];
                }
                $sku = trim((string)($row['sku'] ?? ''));
                if ($sku === '') {
                    $suffix = implode('-', array_map(fn($pair) => \Illuminate\Support\Str::slug($pair['value']), $pairs));
                    $sku = \Illuminate\Support\Str::slug($product->slug) . '-' . $suffix;
                }
                $price = $row['price'] ?? null;
                $isDefault = !empty($row['is_default']) && !$defaultsMarked;
                if ($isDefault) $defaultsMarked = true;
                $items[] = [
                    'pairs' => $pairs,
                    'sku' => $sku,
                    'price' => $price,
                    'is_default' => $isDefault,
                ];
            }
            if (!$defaultsMarked && !empty($items)) {
                $items[0]['is_default'] = true;
            }
            $product->variants_json = $items;
            $product->save();
            
            $this->syncDbVariants($product);
            return $product;
        });

        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được tạo.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = \App\Models\Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(\App\Http\Requests\ProductUpdateRequest $request, Product $product)
    {
        $data = $request->validated();

        $data['stock'] = $data['stock'] ?? 0;
        $data['is_active'] = isset($data['is_active']) ? (bool)$data['is_active'] : false;
        $data['is_clearance'] = isset($data['is_clearance']) ? (bool)$data['is_clearance'] : false;
        $data['description'] = $data['description'] ?? '';

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('image'), $filename);
                $data['image'] = 'image/' . $filename;
            }
        }

        // Handle gallery uploads
        $galleryPaths = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $file) {
                if ($file->isValid()) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('image'), $filename);
                    $galleryPaths[] = 'image/' . $filename;
                }
            }
        }

        \Illuminate\Support\Facades\DB::transaction(function() use ($request, $product, $data, $galleryPaths) {
            $product->update($data);

            if (!empty($galleryPaths)) {
                $sortOrder = (int) $product->productImages()->max('sort_order');
                foreach ($galleryPaths as $path) {
                    $sortOrder++;
                    \App\Models\ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $path,
                        'sort_order' => $sortOrder,
                    ]);
                }
            }

            $variantsInput = (array)$request->input('variants', []);
            $defaultsMarked = false;
            $items = [];
            foreach ($variantsInput as $row) {
                $combo = (string)($row['combo'] ?? '');
                $parts = array_filter(explode('|', $combo));
                $pairs = [];
                foreach ($parts as $p) {
                    [$on, $vv] = array_pad(explode(':', $p, 2), 2, null);
                    $on = trim((string)$on); $vv = trim((string)$vv);
                    if ($on !== '' && $vv !== '') $pairs[] = ['name'=>$on,'value'=>$vv];
                }
                $sku = trim((string)($row['sku'] ?? ''));
                if ($sku === '') {
                    $suffix = implode('-', array_map(fn($pair) => \Illuminate\Support\Str::slug($pair['value']), $pairs));
                    $sku = \Illuminate\Support\Str::slug($product->slug) . '-' . $suffix;
                }
                $price = $row['price'] ?? null;
                $isDefault = !empty($row['is_default']) && !$defaultsMarked;
                if ($isDefault) $defaultsMarked = true;
                $items[] = [
                    'pairs' => $pairs,
                    'sku' => $sku,
                    'price' => $price,
                    'is_default' => $isDefault,
                ];
            }
            if (!$defaultsMarked && !empty($items)) {
                $items[0]['is_default'] = true;
            }
            $product->variants_json = $items;
            $product->save();
            $this->syncDbVariants($product);
        });

        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được cập nhật.');
    }

    /**
     * Remove a gallery image from a product.
     */
    public function destroyGalleryImage(Product $product, ProductImage $image)
    {
        if ($image->product_id !== $product->id) {
            abort(404);
        }

        if (!empty($image->image)) {
            if (Storage::disk('public')->exists($image->image)) {
                Storage::disk('public')->delete($image->image);
            } elseif (file_exists(public_path($image->image))) {
                @unlink(public_path($image->image));
            }
        }

        $image->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // delete image file if exists
        if (!empty($product->image) && file_exists(public_path($product->image))) {
            @unlink(public_path($product->image));
        }
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được xóa.');
    }

    private function syncDbVariants(Product $product): void
    {
        $items = is_array($product->variants_json ?? null) ? $product->variants_json : [];
        // Clear old variants and values
        $old = $product->variants()->with('values')->get();
        foreach ($old as $v) {
            $v->values()->delete();
            $v->delete();
        }
        foreach ($items as $it) {
            $pairs = is_array($it['pairs'] ?? null) ? $it['pairs'] : [];
            $sku = trim((string)($it['sku'] ?? ''));
            if ($sku === '') {
                $suffix = implode('-', array_map(fn($p) => \Illuminate\Support\Str::slug((string)($p['value'] ?? '')), $pairs));
                $sku = \Illuminate\Support\Str::slug($product->slug) . '-' . $suffix;
            }
            $price = $it['price'] ?? null;
            $variantName = collect($pairs)->map(function ($p) {
                $n = trim((string)($p['name'] ?? ''));
                $v = trim((string)($p['value'] ?? ''));
                return ($n !== '' && $v !== '') ? ($n . ':' . $v) : null;
            })->filter()->implode(' / ');
            $variant = \App\Models\ProductVariant::create([
                'product_id' => $product->id,
                'variant_name' => $variantName !== '' ? $variantName : 'Biến thể',
                'sku' => $sku,
                'price' => $price,
                'original_price' => $price,
                'sale_price' => null,
                'stock' => (int)($product->stock ?? 0),
                'is_active' => true,
            ]);
            foreach ($pairs as $p) {
                $name = trim((string)($p['name'] ?? ''));
                $value = trim((string)($p['value'] ?? ''));
                if ($name === '' || $value === '') continue;
                \App\Models\ProductVariantValue::create([
                    'product_variant_id' => $variant->id,
                    'name' => $name,
                    'value' => $value,
                ]);
            }
        }
    }
}
