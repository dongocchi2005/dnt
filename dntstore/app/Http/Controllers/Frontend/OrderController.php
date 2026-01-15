<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function history()
    {
        $orders = auth()->user()->orders()
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('frontend.order-history', compact('orders'));
    }

    public function show($id)
    {
        $order = auth()->user()->orders()
            ->with('items')
            ->findOrFail($id);

        return view('frontend.order-detail', compact('order'));
    }
}
