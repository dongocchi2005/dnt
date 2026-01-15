<?php

namespace App\Services\Chat;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Carbon;

class BookingCreateService
{
    public function create(array $slots, ?int $userId = null): array
    {
        $name = trim((string)($slots['name'] ?? ''));
        $phone = trim((string)($slots['phone'] ?? ''));
        $email = trim((string)($slots['email'] ?? ''));
        $branch = trim((string)($slots['branch'] ?? ''));
        $device = trim((string)($slots['device'] ?? ''));
        $problem = trim((string)($slots['problem'] ?? ''));
        $date = trim((string)($slots['date'] ?? ''));
        $time = trim((string)($slots['time'] ?? ''));

        if ($name === '' || $phone === '' || $device === '' || $problem === '' || $date === '' || $time === '') {
            return [
                'status' => 'invalid',
                'message' => 'Thiếu thông tin đặt lịch.',
            ];
        }

        $user = null;
        if ($userId) {
            $user = User::find($userId);
        }
        if (!$user && $phone !== '') {
            $user = User::where('phone', $phone)->first();
        }

        if (!$user) {
            return [
                'status' => 'require_login',
                'message' => 'Vui lòng đăng nhập để tạo đặt lịch.',
            ];
        }

        $service = Service::first();
        if (!$service) {
            return [
                'status' => 'service_missing',
                'message' => 'Hiện chưa có dịch vụ để đặt lịch.',
            ];
        }

        $dateObj = $this->parseDate($date);
        if (!$dateObj) {
            return [
                'status' => 'invalid_date',
                'message' => 'Ngày đặt lịch không hợp lệ.',
            ];
        }

        $timeSlot = $this->normalizeTimeSlot($time);

        $booking = Booking::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'customer_name' => $name,
            'phone' => $phone,
            'customer_email' => $email !== '' ? $email : null,
            'branch' => $branch !== '' ? $branch : null,
            'booking_date' => $dateObj->format('Y-m-d'),
            'time_slot' => $timeSlot,
            'device_name' => $device,
            'device_issue' => $problem,
            'status' => 'pending',
        ]);

        return [
            'status' => 'created',
            'booking_id' => $booking->id,
            'booking_date' => $dateObj->format('d/m/Y'),
            'time_slot' => $timeSlot,
        ];
    }

    private function parseDate(string $date): ?Carbon
    {
        $date = trim($date);
        if ($date === '') return null;
        try {
            return Carbon::createFromFormat('d/m/Y', $date);
        } catch (\Throwable $e) {
            try {
                return Carbon::parse($date);
            } catch (\Throwable $e) {
                return null;
            }
        }
    }

    private function normalizeTimeSlot(string $time): string
    {
        $lower = mb_strtolower($time);
        if ($lower === 'buổi sáng' || $lower === 'sáng') return 'Buổi sáng';
        if ($lower === 'buổi chiều' || $lower === 'chiều') return 'Buổi chiều';
        if ($lower === 'buổi tối' || $lower === 'tối') return 'Buổi tối';
        return $time !== '' ? $time : 'Any';
    }
}
