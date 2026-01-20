<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Revenue;
use Illuminate\Support\Facades\Schema;
use App\Notifications\PaymentStatusUpdated;

class AdminBookingController extends Controller
{
    public function __construct()
    {
        $this->middleware(\App\Http\Middleware\IsAdmin::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q = request('q');
        $status = request('status');

        $query = Booking::with('user')
            ->when($q, fn($qq) => $qq->search($q))
            ->when($status, fn($qq) => $qq->status($status))
            ->latest();

        $bookings = $query->paginate(20)->appends(request()->query());

        return view('admin.bookings.index', compact('bookings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $booking = Booking::findOrFail($id);
        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $allowedStatuses = [
            'pending','confirmed','completed','cancelled',
            'đang chờ','đã xác nhận','đã hoàn thành','đã hủy'
        ];

        $rules = [
            'status' => 'required|in:'.implode(',', $allowedStatuses),
        ];

        // Only validate price if the bookings.price column exists
        if (Schema::hasColumn('bookings', 'price')) {
            $rules['price'] = 'nullable|numeric|min:0';
        }

        $request->validate($rules);

        $booking = Booking::findOrFail($id);

        // If booking already completed, disallow further updates
        $alreadyCompleted = in_array($booking->status, ['completed', 'đã hoàn thành', 'Đã hoàn thành'], true);
        if ($alreadyCompleted) {
            return redirect()->back()->with('error', 'Đặt lịch đã hoàn thành, không thể cập nhật thêm.');
        }
        $status = $request->input('status');

        // Normalize Vietnamese status values to canonical English values for storage
        $statusMap = [
            'đang chờ' => 'pending',
            'pending' => 'pending',
            'đã xác nhận' => 'confirmed',
            'confirmed' => 'confirmed',
            'đã hoàn thành' => 'completed',
            'Đã hoàn thành' => 'completed',
            'completed' => 'completed',
            'đã hủy' => 'cancelled',
            'cancelled' => 'cancelled',
        ];

        $savedStatus = $statusMap[$status] ?? $status;
        $booking->status = $savedStatus;

        // If marking completed, optionally store price (only if column exists)
        $completedStatuses = ['completed', 'đã hoàn thành', 'Đã hoàn thành'];
        if (in_array($status, $completedStatuses, true) && Schema::hasColumn('bookings', 'price')) {
            $price = $request->input('price');
            if ($price !== null && $price !== '') {
                $booking->price = $price;
            }
        }

        $booking->save();

        return redirect()->back()->with('success', 'Cập nhật trạng thái thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();
        return redirect()->back()->with('success', 'Xóa đặt lịch thành công');
    }
//     public function confirmPayment(\Illuminate\Http\Request $request, \App\Models\Booking $booking)
// {
//     if (!$booking->payment_proof) {
//         return back()->with('error', 'Chưa có ảnh chuyển khoản.');
//     }

//     $booking->update([
//         'payment_status' => 'completed',
//         'status' => 'confirmed', // xác nhận lịch
//     ]);

//     return back()->with('success', 'Đã xác nhận thanh toán đặt lịch.');
// }

// public function rejectPayment(\Illuminate\Http\Request $request, \App\Models\Booking $booking)
// {
//     $booking->update([
//         'payment_status' => 'failed',
//     ]);

//     return back()->with('success', 'Đã từ chối thanh toán đặt lịch.');
// }
 

public function confirmPayment(Request $request, Booking $booking)
{
    // Nếu đã xác nhận rồi thì không ghi trùng doanh thu
    if (($booking->payment_status ?? 'pending') === 'completed') {
        return back()->with('info', 'Đặt lịch này đã được xác nhận trước đó.');
    }

    $booking->update([
        'payment_status' => 'completed',
        'status' => 'confirmed',
    ]);

    // GHI DOANH THU (cho Dashboard)
    Revenue::create([
        'type' => 'booking',
        'amount' => $booking->price ?? 0,
    ]);

    // THÔNG BÁO CHO KHÁCH
    $booking->user?->notify(
        new PaymentStatusUpdated('booking', $booking, 'completed')
    );

    return back()->with('success', 'Đã xác nhận thanh toán đặt lịch.');
}


public function rejectPayment(Request $request, Booking $booking)
{
    // Nếu đã xử lý rồi thì không làm lại
    if (($booking->payment_status ?? 'pending') === 'failed') {
        return back()->with('info', 'Thanh toán này đã bị từ chối trước đó.');
    }

    $booking->update([
        'payment_status' => 'failed',
    ]);

    // Thông báo cho khách
    $booking->user?->notify(
        new PaymentStatusUpdated('booking', $booking, 'failed')
    );

    return back()->with('success', 'Đã từ chối thanh toán đặt lịch.');
}


}
