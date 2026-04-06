<?php

namespace App\DTOs;

use Illuminate\Support\Carbon;

class AvailabilitySearchDTO
{
    public function __construct(
        public readonly string $city,
        public readonly Carbon $checkIn,
        public readonly Carbon $checkOut,
        public readonly int $adults
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            city: $data['city'],
            checkIn: Carbon::parse($data['check_in'])->startOfDay(),
            checkOut: Carbon::parse($data['check_out'])->startOfDay(),
            adults: (int) $data['adults']
        );
    }

    public function nights(): int
    {
        return $this->checkIn->diffInDays($this->checkOut);
    }
}
