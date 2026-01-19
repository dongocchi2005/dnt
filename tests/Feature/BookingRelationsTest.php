<?php

use App\Models\Booking;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('booking has attachments relation', function () {
    $booking = new Booking();

    expect($booking->attachments())->toBeInstanceOf(HasMany::class);
});

