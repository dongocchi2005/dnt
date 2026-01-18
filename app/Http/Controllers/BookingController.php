<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function create()
    {
        return view('frontend.booking');
    }

    public function store(Request $request)
    {
        // Ensure user is authenticated
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'device' => 'required|string|max:255',
            'issue_description' => 'required|string|min:10',
            'receive_method' => 'required|in:store,ship',
            'appointment_at' => 'required_if:receive_method,store|nullable|date',
            'shipping_provider' => 'required_if:receive_method,ship|nullable|string',
            'pickup_address' => 'required_if:receive_method,ship|nullable|string|min:10',
            'photos' => 'array|max:5',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        // Additional validation for shipping code
        if ($request->input('receive_method') === 'ship') {
            $request->validate([
                'shipping_code' => 'required|string|max:255',
            ]);
        }

        // Find a service to attach to the booking (fallback to first service)
        $service = Service::first();
        if (!$service) {
            return redirect()->back()->withErrors(['service' => 'Hiện chưa có dịch vụ để đặt lịch. Vui lòng liên hệ quản trị.']);
        }

        // Normalize phone number
        $phone = $request->input('phone');
        $phone = preg_replace('/\D/', '', $phone); // Remove non-numeric characters
        if (!str_starts_with($phone, '0') && !str_starts_with($phone, '+84')) {
            $phone = '0' . $phone;
        }

        // Prepare data
        $data = [
            'user_id' => $user->id,
            'service_id' => $service->id,
            'customer_name' => $request->input('name'),
            'phone' => $phone,
            'device_name' => $request->input('device'),
            'device_issue' => $request->input('issue_description'),
            'receive_method' => $request->input('receive_method'),
            'shipping_provider' => $request->input('shipping_provider'),
            'pickup_address' => $request->input('pickup_address'),
            'status' => 'pending',
        ];

        // Only add appointment_at if it is provided and column exists (or just passed if provided)
        // Since we are having migration issues on VPS, we can temporarily exclude it if null, or just check input
        if ($request->filled('appointment_at')) {
             $data['appointment_at'] = $request->input('appointment_at');
        }

        // Add shipping_code if present (it's not in fillable usually, check model)
        if ($request->has('shipping_code')) {
             // Assuming you might want to store this somewhere, but based on error it was not in the insert list causing issue if it was.
             // But wait, the error was about 'appointment_at'.
             // The error "Column not found: 1054 Unknown column 'appointment_at'" confirms DB schema is missing it.
             // We are fixing the code to match schema OR fixing schema.
             // User wants to FIX the error.
             // If migration failed on VPS, we should remove 'appointment_at' from here temporarily IF we cannot migrate.
             // BUT user said "git pull" and "migrate" showed "Nothing to migrate".
             // This implies migration MIGHT have been marked as run but column is missing?
             // OR migration file is new and not on VPS?
             // Image 1 shows "2026_01_17_... DONE" is NOT in the list.
             // Wait, Image 1 shows:
             // 2025_12_24_084351_create_bookings_table ... DONE
             // ...
             // It does NOT show 2026_01_17_000000_add_appointment_at_to_bookings_table.php in the list of "Nothing to migrate" implies it thinks it is done?
             // No, "Nothing to migrate" means all files in `migrations` folder are recorded in `migrations` table.
             // IF the file `2026_01_17...` exists on VPS but `migrate` says nothing, it means it is in the table.
             // IF it is in the table but column is missing in DB -> DB is out of sync.
             // We should create a NEW migration to add it again or check if file exists.
        }

        // Add notes if provided
        if ($request->input('notes')) {
            $data['notes'] = $request->input('notes');
        }

        $booking = Booking::create($data);

        // Handle photo uploads
        if ($request->hasFile('photos')) {
            $photos = $request->file('photos');
            foreach ($photos as $photo) {
                $path = $photo->store('bookings/' . $booking->id, 'public');
                $booking->attachments()->create([
                    'path' => $path,
                    'original_name' => $photo->getClientOriginalName(),
                    'mime' => $photo->getMimeType(),
                    'size' => $photo->getSize(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Đặt lịch thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất.');
    }

    public function history()
    {
        $bookings = Booking::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('frontend.booking-history', compact('bookings'));
    }

    public function cancel(Request $request, $id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$booking) {
            return response()->json([
                'ok' => false,
                'message' => 'Đơn đặt lịch không tồn tại hoặc bạn không có quyền hủy.'
            ], 404);
        }

        // Only allow canceling pending bookings
        if ($booking->status_key !== 'pending') {
            return response()->json([
                'ok' => false,
                'message' => 'Chỉ có thể hủy đơn đặt lịch đang chờ xác nhận.'
            ], 400);
        }

        // Update status to cancelled
        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'ok' => true,
            'message' => 'Đã hủy đặt lịch thành công.'
        ]);
    }
}
