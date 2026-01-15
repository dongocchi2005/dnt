<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $userId = $request->user()->id;

        $hasPurchased = Order::where('user_id', $userId)
            ->whereHas('items', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })
            ->where(function ($q) {
                $q->whereNotNull('delivered_at')
                  ->orWhere('order_status', 'delivered');
            })
            ->exists();

        if (!$hasPurchased) {
            return back()->with('error', 'Bạn cần mua sản phẩm này trước khi đánh giá.');
        }

        $payload = [
            'rating' => $data['rating'],
            'content' => $data['comment'] ?? null,
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('product_reviews', 'is_approved')) {
            $payload['is_approved'] = 1;
        }

        ProductReview::updateOrCreate(
            ['product_id' => $product->id, 'user_id' => $userId],
            $payload
        );

        return back()->with('success', 'Cảm ơn bạn đã đánh giá.');
    }
}
