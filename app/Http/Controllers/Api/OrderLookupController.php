<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Chat\OrderLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderLookupController extends Controller
{
    public function __invoke(Request $request, OrderLookupService $lookup): JsonResponse
    {
        $data = $request->validate([
            'order_code' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
        ]);

        if (empty($data['order_code']) && empty($data['phone'])) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp mã đơn hoặc số điện thoại.',
                'orders' => [],
            ], 422);
        }

        $orders = $lookup->lookup($data['order_code'] ?? null, $data['phone'] ?? null);

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }
}
