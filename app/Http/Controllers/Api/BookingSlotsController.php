<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BookingSlotsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingSlotsController extends Controller
{
    public function __invoke(Request $request, BookingSlotsService $slotsService): JsonResponse
    {
        $data = $request->validate([
            'date' => 'nullable|string|max:20',
            'branch' => 'nullable|string|max:100',
        ]);

        $slots = $slotsService->getSlots($data['date'] ?? null, $data['branch'] ?? null);

        return response()->json([
            'success' => true,
            'slots' => $slots,
        ]);
    }
}
