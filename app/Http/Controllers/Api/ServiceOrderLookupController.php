<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Chat\ServiceOrderLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceOrderLookupController extends Controller
{
    public function __invoke(Request $request, ServiceOrderLookupService $lookup): JsonResponse
    {
        $data = $request->validate([
            'code' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
        ]);

        if (empty($data['code']) && empty($data['phone'])) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp mã SO hoặc số điện thoại.',
                'service_orders' => [],
            ], 422);
        }

        $orders = $lookup->lookup($data['code'] ?? null, $data['phone'] ?? null);

        return response()->json([
            'success' => true,
            'service_orders' => $orders,
        ]);
    }
}
