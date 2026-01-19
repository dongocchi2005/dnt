<?php

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;

test('user can create booking with receive_method shipping', function () {
    $user = User::factory()->create();

    Service::create([
        'name' => 'Sửa chữa test',
        'description' => 'Dịch vụ test',
        'price' => 100000,
        'status' => 'active',
    ]);

    $response = $this
        ->actingAs($user)
        ->from('/booking')
        ->post('/booking', [
            'name' => 'Đỗ Ngọc Thân',
            'phone' => '+84987833560',
            'device' => 'iPhone 13',
            'issue_description' => 'Màn hình bị nhòe màu',
            'receive_method' => 'shipping',
            'shipping_provider' => 'Grab',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/booking');

    $booking = Booking::query()->first();
    expect($booking)->not->toBeNull();
    expect($booking->receive_method)->toBe('ship');
    expect($booking->shipping_provider)->toBe('Grab');
});

