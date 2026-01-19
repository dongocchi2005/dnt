<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\Schema;
use App\Notifications\PaymentStatusUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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

        if (Schema::hasColumn('bookings', 'price')) {
            $rules['price'] = 'required_if:status,completed|nullable|numeric|min:0';
        }

        $rawPrice = $request->input('price');
        $normalizedPrice = $this->normalizeMoneyInput($rawPrice);
        if ($normalizedPrice !== null) {
            $request->merge(['price' => $normalizedPrice]);
        }

        $validator = Validator::make($request->all(), $rules, [
            'price.required_if' => 'Vui lòng nhập giá khi chuyển sang trạng thái "Đã hoàn thành".',
            'price.numeric' => 'Giá không hợp lệ. Ví dụ hợp lệ: 200000 hoặc 200.000 hoặc 200,000',
        ]);

        if ($validator->fails()) {
            Log::warning('Admin booking update validation failed', [
                'booking_id' => $id,
                'admin_user_id' => $request->user()?->id,
                'status' => $request->input('status'),
                'price_raw' => $rawPrice,
                'price_normalized' => $normalizedPrice,
                'errors' => $validator->errors()->toArray(),
            ]);

            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Cập nhật thất bại. Vui lòng kiểm tra lại dữ liệu nhập.');
        }

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

    private function normalizeMoneyInput(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $s = trim($value);
        if ($s === '') {
            return null;
        }

        $s = preg_replace('/[^\d\.,]/u', '', $s);
        if ($s === null || $s === '') {
            return null;
        }

        $hasDot = str_contains($s, '.');
        $hasComma = str_contains($s, ',');

        if ($hasDot && $hasComma) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } elseif ($hasComma) {
            if (preg_match('/,\d{1,2}$/', $s) === 1) {
                $s = str_replace(',', '.', $s);
            } else {
                $s = str_replace(',', '', $s);
            }
        } elseif ($hasDot) {
            if (preg_match('/\.\d{1,2}$/', $s) !== 1) {
                $s = str_replace('.', '', $s);
            }
        }

        $s = preg_replace('/\.(?=.*\.)/', '', $s);

        return $s ?: null;
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
