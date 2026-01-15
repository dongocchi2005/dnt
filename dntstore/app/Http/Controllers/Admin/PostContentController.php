<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class PostContentController extends Controller
{
    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file|image|mimes:jpg,jpeg,png,webp|max:10048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $path = $request->file('image')->store('posts', 'public');
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Không thể lưu ảnh. Vui lòng thử lại.',
            ], 500);
        }

        return response()->json([
            'url' => '/storage/' . ltrim($path, '/'),
        ]);
    }
}
