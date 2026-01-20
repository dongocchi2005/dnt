<?php

use App\Models\Booking;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('allows mass-assigning payment fields on Booking', function () {
    $fillable = (new Booking())->getFillable();

    expect($fillable)->toContain('payment_proof');
    expect($fillable)->toContain('payment_status');
    expect($fillable)->toContain('payment_method');
    expect($fillable)->toContain('transaction_id');
    expect($fillable)->toContain('price');
});

it('exposes attachments relation on Booking', function () {
    $booking = new Booking();

    expect($booking->attachments())->toBeInstanceOf(HasMany::class);
});

