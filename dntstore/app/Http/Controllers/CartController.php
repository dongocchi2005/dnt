<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function cartKey(): string
    {
        return 'cart.items';
    }

    /**
     * Generate a unique cart key based on product and options.
     * Format: product_id:variant_id|base:color|na:size|na:options_md5
     */
    private function generateItemKey(int $productId, $variantId, ?string $color, ?string $size, array $otherOptions = []): string
    {
        $vId = $variantId ? (string)$variantId : 'base';
        $c = $color ? md5(trim($color)) : 'na';
        $s = $size ? md5(trim($size)) : 'na';
        
        // Sort other options to ensure consistency
        ksort($otherOptions);
        $o = !empty($otherOptions) ? md5(json_encode($otherOptions)) : 'na';

        return implode(':', [$productId, $vId, $c, $s, $o]);
    }

    public function index()
    {
        $items = session($this->cartKey(), []);
        $subtotal = collect($items)->sum(fn($i) => (float)($i['price'] ?? 0) * (int)($i['qty'] ?? 0));
        return view('frontend.cart.index', compact('items', 'subtotal'));
    }

    public function add(Request $request, Product $product)
    {
        if ($request->isJson()) {
            $request->merge($request->json()->all());
        }

        $qty = max(1, (int)$request->input('qty', 1));
        $variantId = $request->input('variant_id');
        $variantKey = $request->input('variant_key'); // Legacy support (ignored for pricing)
        
        // Capture Options
        $color = null;
        $size = null;
        $options = []; // Deprecated options, keep structure for display

        // Normalize inputs
        if ($color) $options['color'] = $color;
        if ($size) $options['size'] = $size;

        // Determine effective variant info
        $hasVariants = $product->variants()->where('is_active', true)->exists();

        $finalVariantId = null;
        $finalPrice = $product->display_price ?? $product->price ?? 0;
        $finalName = $product->name;
        $finalImage = $product->image;
        $variantName = '';
        $finalSku = null;

        if ($hasVariants) {
            if (!$variantId) {
                return $this->responseFail('Vui lòng chọn đầy đủ biến thể.');
            }

            $variant = ProductVariant::where('id', $variantId)
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->first();

            if (!$variant) {
                return $this->responseFail('Biến thể không hợp lệ.');
            }

            if ((int)($variant->stock ?? 0) < $qty) {
                return $this->responseFail('Số lượng vượt quá tồn kho.');
            }

            $finalVariantId = $variant->id;
            $finalPrice = $variant->sale_price ?? $variant->price ?? $variant->original_price ?? $finalPrice;
            $finalSku = $variant->sku;
            $variantName = $variant->values()
                ->get()
                ->map(function ($vv) {
                    $n = trim((string)($vv->name ?? ''));
                    $v = trim((string)($vv->value ?? ''));
                    return ($n !== '' && $v !== '') ? ($n . ': ' . $v) : null;
                })
                ->filter()
                ->implode(', ');
        } else {
            if ((int)($product->stock ?? 0) > 0 && (int)($product->stock ?? 0) < $qty) {
                return $this->responseFail('Số lượng vượt quá tồn kho.');
            }
        }

        // Generate Unique Key
        $cartKey = $this->generateItemKey(
            $product->id, 
            $finalVariantId, 
            $options['color'] ?? null, 
            $options['size'] ?? null,
            array_diff_key($options, ['color' => 1, 'size' => 1])
        );

        $cart = session($this->cartKey(), []);

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['qty'] += $qty;
        } else {
            // Build Variant Name for display if not set
            if (empty($variantName)) {
                $parts = [];
                if (!empty($options['color'])) $parts[] = $options['color'];
                if (!empty($options['size'])) $parts[] = $options['size'];
                $variantName = implode(' / ', $parts);
            }

            $cart[$cartKey] = [
                'key'          => $cartKey, // Store key for easy access
                'id'           => $product->id,
                'variant_id'   => $finalVariantId,
                'name'         => $finalName,
                'variant_name' => $variantName,
                'variant_sku'  => $finalSku,
                'price'        => (float)$finalPrice,
                'image'        => (string)$finalImage,
                'qty'          => $qty,
                'options'      => $options,
            ];
        }

        session([$this->cartKey() => $cart]);

        return $this->responseSuccess($cart, 'Đã thêm vào giỏ hàng', ['item_key' => $cartKey]);
    }

    public function updateQty(Request $request)
    {
        $key = $request->input('key');
        $qty = max(1, (int)$request->input('qty', 1));

        $cart = session($this->cartKey(), []);

        if (isset($cart[$key])) {
            $variantId = $cart[$key]['variant_id'] ?? null;
            $productId = (int)($cart[$key]['id'] ?? 0);
            if ($variantId) {
                $variant = ProductVariant::where('id', $variantId)->where('product_id', $productId)->first();
                $max = (int)($variant?->stock ?? 0);
                if ($max > 0) {
                    $qty = min($qty, $max);
                }
            } else {
                $product = Product::find($productId);
                $max = (int)($product?->stock ?? 0);
                if ($max > 0) {
                    $qty = min($qty, $max);
                }
            }
            $cart[$key]['qty'] = $qty;
            session([$this->cartKey() => $cart]);
        }

        return $this->responseSuccess($cart, 'Đã cập nhật số lượng', [
            'item_line_total' => isset($cart[$key]) ? ($cart[$key]['price'] * $cart[$key]['qty']) : 0,
            'qty' => $qty
        ]);
    }

    // Legacy update route support (mapped to /cart/update/{key})
    public function update(Request $request, string $key)
    {
        $request->merge(['key' => $key]);
        return $this->updateQty($request);
    }

    public function remove(Request $request, string $key)
    {
        $cart = session($this->cartKey(), []);
        if (isset($cart[$key])) {
            unset($cart[$key]);
            session([$this->cartKey() => $cart]);
        }

        return $this->responseSuccess($cart, 'Đã xoá sản phẩm');
    }

    public function removeSelected(Request $request)
    {
        $keys = $request->input('keys', []);
        $cart = session($this->cartKey(), []);
        
        foreach ($keys as $key) {
            unset($cart[$key]);
        }
        session([$this->cartKey() => $cart]);

        return $this->responseSuccess($cart, 'Đã xoá sản phẩm đã chọn');
    }

    public function clear()
    {
        session()->forget($this->cartKey());
        return back()->with('success', 'Đã xoá giỏ hàng');
    }

    public function count()
    {
        $cart = session($this->cartKey(), []);
        return response()->json([
            'success' => true,
            'cart_count' => $this->countNumber($cart)
        ]);
    }

    private function countNumber(array $cart): int
    {
        return (int) collect($cart)->sum(fn($i) => (int)($i['qty'] ?? 0));
    }

    private function responseSuccess($cart, $msg, $extras = [])
    {
        $subtotal = collect($cart)->sum(fn($i) => (float)($i['price'] ?? 0) * (int)($i['qty'] ?? 0));
        
        $data = array_merge([
            'success' => true,
            'cart_count' => $this->countNumber($cart),
            'subtotal' => $subtotal,
            'message' => $msg,
        ], $extras);

        if (request()->expectsJson() || request()->isJson()) {
            return response()->json($data);
        }

        return back()->with('success', $msg);
    }

    private function responseFail(string $msg)
    {
        if (request()->expectsJson() || request()->isJson()) {
            return response()->json(['success' => false, 'message' => $msg], 422);
        }
        return back()->with('error', $msg);
    }
}
