<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Carbon;

class BookingSlotsService
{
    public function getSlots(?string $date, ?string $branch = null): array
    {
        $slots = config('booking.slots', []);
        if (!$slots) {
            $slots = [
                ['label' => 'Buổi sáng', 'start' => '09:00', 'end' => '12:00', 'capacity' => 6],
                ['label' => 'Buổi chiều', 'start' => '13:30', 'end' => '17:30', 'capacity' => 6],
                ['label' => 'Buổi tối', 'start' => '18:30', 'end' => '20:30', 'capacity' => 4],
            ];
        }

        $dateObj = null;
        if ($date) {
            try {
                $dateObj = Carbon::createFromFormat('d/m/Y', $date);
            } catch (\Throwable $e) {
                try {
                    $dateObj = Carbon::parse($date);
                } catch (\Throwable $e) {
                    $dateObj = null;
                }
            }
        }

        $result = [];
        foreach ($slots as $slot) {
            $label = (string)($slot['label'] ?? '');
            $capacity = (int)($slot['capacity'] ?? 0);
            $booked = 0;

            if ($dateObj && $label !== '') {
                $query = Booking::query()->whereDate('booking_date', $dateObj->format('Y-m-d'));
                $query->where('time_slot', $label);
                if ($branch) {
                    $query->where('branch', $branch);
                }
                $query->whereNotIn('status', ['cancelled', 'đã hủy']);
                $booked = (int)$query->count();
            }

            $remaining = max(0, $capacity - $booked);

            $result[] = [
                'label' => $label,
                'start' => (string)($slot['start'] ?? ''),
                'end' => (string)($slot['end'] ?? ''),
                'capacity' => $capacity,
                'booked' => $booked,
                'remaining' => $remaining,
                'available' => $remaining > 0,
            ];
        }

        return $result;
    }
}
