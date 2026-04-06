<?php

namespace App\Services\Contracts;

use App\DTOs\CreateBookingDTO;
use App\Models\Booking;

interface BookingServiceInterface
{
    /**
     * Create a new booking with overbooking prevention.
     */
    public function book(CreateBookingDTO $dto): Booking;

    /**
     * Cancel an existing booking.
     */
    public function cancel(Booking $booking): Booking;
}
