<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Chat\BookingCreateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingCreateController extends Controller
{
    public function __invoke(Request $request, BookingCreateService $creator): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'branch' => 'nullable|string|max:255',
            'device' => 'required|string|max:255',
            'problem' => 'required|string|max:1000',
            'date' => 'required|string|max:20',
            'time' => 'required|string|max:50',
        ]);

        $result = $creator->create($data, auth()->id());
        $status = ($result['status'] ?? '') === 'created' ? 201 : 422;

        return response()->json([
            'success' => $status === 201,
            'result' => $result,
        ], $status);
    }
}
