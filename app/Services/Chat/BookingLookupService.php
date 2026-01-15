<?php

namespace App\Services\Chat;

use App\Models\Booking;

class BookingLookupService
{
    public function lookup(?string $bookingCode, ?string $phone): array
    {
        $bookingCode = $bookingCode ? trim((string)$bookingCode) : null;
        $phone = $phone ? trim((string)$phone) : null;

        $query = Booking::query()->with('service');

        if ($bookingCode) {
            $query->where('id', (int)$bookingCode);
        }

        if ($phone) {
            $query->where('phone', $phone);
        }

        if (!$bookingCode && !$phone) {
            return [];
        }

        return $query->latest()->limit(3)->get()->map(function (Booking $booking) {
            return [
                'id' => $booking->id,
                'status' => $booking->status_label ?? $booking->status,
                'booking_date' => optional($booking->booking_date)->format('d/m/Y'),
                'time_slot' => $booking->time_slot,
                'device_name' => $booking->device_name,
                'device_issue' => $booking->device_issue,
                'service' => $booking->service?->name,
            ];
        })->all();
    }
}
