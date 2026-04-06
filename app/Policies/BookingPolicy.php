<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine if the user can view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id;
    }

    /**
     * Determine if the user can cancel the booking.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id;
    }
}
