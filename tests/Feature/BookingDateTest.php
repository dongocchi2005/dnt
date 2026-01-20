<?php

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Carbon;

it('sets booking_date from appointment_at and saves successfully', function () {
    $user = User::factory()->create();
    $service = Service::create([
        'name' => 'Repair',
        'description' => 'Repair service',
        'price' => 100000,
        'status' => 'active',
    ]);

    $booking = Booking::create([
        'user_id' => $user->id,
        'service_id' => $service->id,
        'customer_name' => 'Test User',
        'phone' => '0123456789',
        'device_name' => 'iPhone',
        'device_issue' => 'Broken screen',
        'receive_method' => 'store',
        'status' => 'pending',
        'appointment_at' => '2026-01-20 14:30:00',
    ]);

    expect($booking->exists)->toBeTrue();
    expect($booking->booking_date->toDateString())->toBe('2026-01-20');
    expect($booking->time_slot)->toBe('14:30');
});

it('still saves when appointment_at is missing and keeps booking_date consistent', function () {
    Carbon::setTestNow(Carbon::parse('2026-01-20 09:15:00'));

    $user = User::factory()->create();
    $service = Service::create([
        'name' => 'Repair',
        'description' => 'Repair service',
        'price' => 100000,
        'status' => 'active',
    ]);

    $booking = Booking::create([
        'user_id' => $user->id,
        'service_id' => $service->id,
        'customer_name' => 'Test User',
        'phone' => '0123456789',
        'device_name' => 'Samsung',
        'device_issue' => 'Battery issue',
        'receive_method' => 'ship',
        'status' => 'pending',
    ]);

    expect($booking->exists)->toBeTrue();
    expect($booking->appointment_at->toDateTimeString())->toBe('2026-01-20 09:15:00');
    expect($booking->booking_date->toDateString())->toBe('2026-01-20');
    expect($booking->time_slot)->toBe('ship');
});

