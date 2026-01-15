<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminClearanceController extends Controller
{
    public function index()
    {
        $products = Product::where('is_clearance', true)->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.clearance.index', compact('products'));
    }

    public function create()
    {
        return view('admin.clearance.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'original_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:5120',
            'is_active' => 'nullable|boolean',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }

        if (request()->hasFile('image')) {
            $data['image'] = request()->file('image')->store('products', 'public');
        }

        $data['is_clearance'] = true;
        $data['is_active'] = isset($data['is_active']) ? (bool)$data['is_active'] : true;

        Product::create($data);

        return redirect()->route('admin.clearance.index')->with('success', 'Sản phẩm clearance đã được tạo.');
    }

    public function edit(Product $clearance)
    {
        return view('admin.clearance.edit', ['product' => $clearance]);
    }

    public function update(Request $request, Product $clearance)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $clearance->id,
            'description' => 'nullable|string',
            'original_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:5120',
            'is_active' => 'nullable|boolean',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $data['is_clearance'] = true;
        $data['is_active'] = isset($data['is_active']) ? (bool)$data['is_active'] : $clearance->is_active;

        $clearance->update($data);

        return redirect()->route('admin.clearance.index')->with('success', 'Sản phẩm clearance đã được cập nhật.');
    }

    public function destroy(Product $clearance)
    {
        $clearance->delete();
        return redirect()->route('admin.clearance.index')->with('success', 'Sản phẩm clearance đã bị xóa.');
    }
}
