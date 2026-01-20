<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Service;
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
        $receiveMethod = request('receive_method');
        $serviceId = request('service_id');
        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        $query = Booking::query()
            ->with(['user', 'service'])
            ->when($q, function ($qq) use ($q) {
                $like = '%' . $q . '%';
                $qq->where(function ($sub) use ($like) {
                    $sub->where('customer_name', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('device_name', 'like', $like)
                        ->orWhere('device_issue', 'like', $like)
                        ->orWhereHas('user', function ($u) use ($like) {
                            $u->where('name', 'like', $like)->orWhere('email', 'like', $like);
                        });
                });
            })
            ->when($status, function ($qq) use ($status) {
                $normalized = strtolower((string)$status);
                $statusMap = [
                    'pending' => ['pending', 'đang chờ'],
                    'confirmed' => ['confirmed', 'đã xác nhận'],
                    'completed' => ['completed', 'đã hoàn thành', 'Đã hoàn thành'],
                    'cancelled' => ['cancelled', 'đã hủy'],
                ];
                $values = $statusMap[$normalized] ?? [$status];
                $qq->whereIn('status', $values);
            })
            ->when($receiveMethod, fn($qq) => $qq->where('receive_method', $receiveMethod))
            ->when($serviceId, fn($qq) => $qq->where('service_id', $serviceId))
            ->when($dateFrom, fn($qq) => $qq->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($qq) => $qq->whereDate('created_at', '<=', $dateTo))
            ->latest();

        $bookings = $query->paginate(20)->appends(request()->query());
        $services = Service::query()->select('id', 'name')->orderBy('name')->get();

        return view('admin.bookings.index', compact('bookings', 'services'));
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
        $booking = Booking::with(['user', 'service', 'attachments'])->findOrFail($id);
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
        $booking = Booking::findOrFail($id);

        $alreadyCompleted = in_array($booking->status, ['completed', 'đã hoàn thành', 'Đã hoàn thành'], true);

        $allowedStatuses = [
            'pending','confirmed','completed','cancelled',
            'đang chờ','đã xác nhận','đã hoàn thành','đã hủy'
        ];

        if ($alreadyCompleted) {
            $allowedStatuses = ['completed', 'đã hoàn thành', 'Đã hoàn thành'];
        }

        $rules = [
            'status' => 'required|in:'.implode(',', $allowedStatuses),
            'repair_note' => 'nullable|string',
        ];

        // Only validate price if the bookings.price column exists
        if (Schema::hasColumn('bookings', 'price')) {
            $rules['price'] = 'nullable|numeric|min:0';
        }

        $request->validate($rules);

        $repairNote = $request->input('repair_note');
        if (is_string($repairNote)) {
            $repairNote = trim($repairNote);
        }
        $repairNote = ($repairNote === '' ? null : $repairNote);

        if ($alreadyCompleted) {
            Booking::query()
                ->whereKey($booking->id)
                ->update(['repair_note' => $repairNote]);

            return redirect()->back()->with('success', 'Cập nhật ghi chú kỹ thuật thành công');
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

        $booking->repair_note = $repairNote;

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
