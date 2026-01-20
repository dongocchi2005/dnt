<?php

use App\Models\Booking;
use App\Models\BookingAttachment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('stores booking for store receive_method and saves attachments', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    Service::create([
        'name' => 'Repair',
        'description' => 'Repair service',
        'price' => 100000,
        'status' => 'active',
    ]);

    $response = $this
        ->actingAs($user)
        ->from('/booking')
        ->post('/booking', [
            'name' => 'Nguyễn Văn A',
            'phone' => '0327615473',
            'device' => 'iPhone 13',
            'issue_description' => 'Mô tả lỗi đủ dài',
            'receive_method' => 'store',
            'appointment_at' => '2026-02-06 10:58:00',
            'photos' => [
                UploadedFile::fake()->create('a.jpg', 50, 'image/jpeg'),
            ],
        ]);

    $response->assertStatus(302);
    $response->assertSessionHas('success');

    $booking = Booking::query()->latest('id')->first();
    expect($booking)->not->toBeNull();
    expect($booking->booking_date->toDateString())->toBe('2026-02-06');
    expect($booking->time_slot)->toBe('10:58');

    $attachment = BookingAttachment::query()->where('booking_id', $booking->id)->first();
    expect($attachment)->not->toBeNull();
    Storage::disk('public')->assertExists($attachment->path);
});

it('stores booking for ship receive_method and accepts legacy shipping value', function () {
    $user = User::factory()->create();
    Service::create([
        'name' => 'Repair',
        'description' => 'Repair service',
        'price' => 100000,
        'status' => 'active',
    ]);

    $response = $this
        ->actingAs($user)
        ->from('/booking')
        ->post('/booking', [
            'name' => 'Nguyễn Văn B',
            'phone' => '0327615473',
            'device' => 'iPhone 13',
            'issue_description' => 'Mô tả lỗi đủ dài',
            'receive_method' => 'shipping',
            'shipping_provider' => 'SPX',
            'pickup_address' => '123 Đường ABC, Quận 1, TP.HCM',
            'shipping_code' => 'SPX123456789',
        ]);

    $response->assertStatus(302);
    $response->assertSessionHas('success');

    $booking = Booking::query()->latest('id')->first();
    expect($booking)->not->toBeNull();
    expect($booking->receive_method)->toBe('ship');
});
