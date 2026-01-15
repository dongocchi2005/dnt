<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    private function normalizeVariantKey(string $key): string
    {
        $parts = collect(explode('|', $key))
            ->map(fn($s) => trim((string)$s))
            ->filter(fn($s) => $s !== '')
            ->values()
            ->all();

        sort($parts, SORT_STRING);
        return implode('|', $parts);
    }

    private function imageUrl(?string $path): string
    {
        if (!$path) {
            return asset('image/no-image.png');
        }
        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }
        if (Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }
        return asset($path);
    }

    private function variantLabelFromModel(ProductVariant $variant): ?string
    {
        $attrs = $variant->values()->get()
            ->map(function ($vv) {
                $on = $vv->name ?? null;
                $vn = $vv->value ?? null;
                return ($on && $vn) ? ($on . ': ' . $vn) : null;
            })->filter()->implode(', ');

        return $attrs !== '' ? $attrs : null;
    }

    private function resolveUnitPrice(Product $product, ?string $variantId, string $variantKey): float
    {
        $unitPrice = (float)($product->display_price ?? $product->price ?? 0);

        if ($variantId) {
            $variant = ProductVariant::where('id', $variantId)
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->first();
            if ($variant) {
                return (float)($variant->sale_price ?? $variant->price ?? $unitPrice);
            }
        }

        return $unitPrice;
    }

    public function buyNow(Request $request, Product $product)
    {
        $qty = max(1, (int)$request->query('qty', 1));

        $variantId = $request->query('variant_id');
        $variantKey = trim((string)$request->query('variant_key', ''));

        $variantLabel = null;
        $unitPrice = (float)($product->display_price ?? $product->price ?? 0);
        $color = trim((string)$request->query('color', ''));
        $size = trim((string)$request->query('size', ''));

        $hasVariants = $product->activeVariants()->exists();

        if ($variantId) {
            $variant = ProductVariant::where('id', $variantId)
                ->where('product_id', $product->id)
                ->first();
            if ($variant) {
                $variantLabel = $this->variantLabelFromModel($variant);
                $unitPrice = (float)($variant->sale_price ?? $variant->price ?? $variant->original_price ?? 0);
            } else {
                $variantId = null;
            }
        }

        // variants_json no longer used for price resolution

        // color/size on product not used anymore

        if (!$variantId && $variantKey === '' && $hasVariants) {
            $defaultVariant = $product->defaultVariant ?: $product->activeVariants()->first();
            if ($defaultVariant) {
                $variantId = $defaultVariant->id;
                $variantLabel = $this->variantLabelFromModel($defaultVariant);
                $unitPrice = (float)($defaultVariant->sale_price ?? $defaultVariant->price ?? 0);
            } else {
                $unitPrice = (float)$unitPrice;
            }
        }

        $shippingFee = 0;
        $subTotal = $unitPrice * $qty;
        $grandTotal = $subTotal + $shippingFee;

        $product->image_url = $this->imageUrl($product->image ?? null);

        return view('frontend.clearance.checkout.index', compact(
            'product',
            'qty',
            'variantId',
            'variantKey',
            'variantLabel',
            'unitPrice',
            'subTotal',
            'shippingFee',
            'grandTotal'
        ));
    }

    public function place(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cod,vietqr'],
            'product_id' => ['required', 'integer'],
            'qty' => ['required', 'integer', 'min:1'],
            'variant_id' => ['nullable', 'integer'],
        ]);

        $product = Product::findOrFail($request->input('product_id'));
        $qty = max(1, (int)$request->input('qty', 1));
        $variantId = $request->input('variant_id');
        $variantKey = trim((string)$request->input('variant_key', ''));
        $variantLabel = null;
        $variantSku = null;
        $hasVariants = $product->activeVariants()->exists();

        if ($variantId) {
            $variant = ProductVariant::where('id', $variantId)
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->first();
            if ($variant) {
                $variantLabel = $this->variantLabelFromModel($variant);
                $variantSku = $variant->sku ?? null;
            } else {
                $variantId = null;
            }
        }

        if (!$variantId && $variantKey === '' && $hasVariants) {
            $defaultVariant = $product->defaultVariant ?: $product->activeVariants()->first();
            if ($defaultVariant) {
                $variantId = $defaultVariant->id;
                $variantLabel = $this->variantLabelFromModel($defaultVariant);
                $variantSku = $defaultVariant->sku ?? null;
            }
        }

        $unitPrice = $this->resolveUnitPrice($product, $variantId, $variantKey);
        $totalAmount = $unitPrice * $qty;

        $paymentMethod = $request->input('payment_method');
        $paymentMethodDb = $paymentMethod === 'cod' ? 'cash_on_delivery' : 'vietqr';

        $order = DB::transaction(function () use ($product, $qty, $unitPrice, $totalAmount, $paymentMethodDb, $variantId, $variantLabel, $variantSku) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'payment_status' => 'pending',
                'payment_method' => $paymentMethodDb,
                'transaction_id' => $paymentMethodDb === 'cash_on_delivery'
                    ? 'cod_' . time()
                    : 'order_' . time(),
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name ?? 'Sản phẩm',
                'variant_id' => $variantId ? (int)$variantId : null,
                'variant_label' => $variantLabel,
                'variant_sku' => $variantSku,
                'product_image' => $product->image ?? '',
                'price' => $unitPrice,
                'quantity' => $qty,
                'subtotal' => $unitPrice * $qty,
            ]);

            return $order;
        });

        if ($paymentMethod === 'cod') {
            return redirect()
                ->route('payment.thankYou', ['type' => 'order', 'id' => $order->id])
                ->with('success', 'Đơn hàng đã được tạo thành công.');
        }

        return redirect()->route('payment.order', $order->id);
    }
}
