<?php

namespace App\DTOs;

use Illuminate\Support\Carbon;

class CreateBookingDTO
{
    public function __construct(
        public readonly int $hotelId,
        public readonly int $roomTypeId,
        public readonly int $userId,
        public readonly string $guestName,
        public readonly string $guestEmail,
        public readonly Carbon $checkIn,
        public readonly Carbon $checkOut,
        public readonly int $roomsCount,
        public readonly int $adultsCount
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hotelId: (int) $data['hotel_id'],
            roomTypeId: (int) $data['room_type_id'],
            userId: (int) $data['user_id'],
            guestName: $data['guest_name'],
            guestEmail: $data['guest_email'],
            checkIn: Carbon::parse($data['check_in'])->startOfDay(),
            checkOut: Carbon::parse($data['check_out'])->startOfDay(),
            roomsCount: (int) $data['rooms_count'],
            adultsCount: (int) $data['adults_count']
        );
    }
}
