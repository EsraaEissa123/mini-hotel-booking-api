<?php

namespace App\Services;

use App\DTOs\AvailabilitySearchDTO;
use App\Models\Hotel;
use App\Services\Contracts\AvailabilityServiceInterface;
use App\Services\Contracts\PricingServiceInterface;
use Illuminate\Support\Collection;

class AvailabilityService implements AvailabilityServiceInterface
{
    public function __construct(
        private readonly PricingServiceInterface $pricingService
    ) {}


    public function search(AvailabilitySearchDTO $dto): Collection
    {
        // Query active hotels in the given city, eager load room types that can fit the adults
        $hotels = Hotel::active()
            ->inCity($dto->city)
            ->with(['roomTypes' => function ($query) use ($dto) {
                // To fit the adults, we just need room_types where their max_capacity >= adults.
                // (Assuming 1 room search by default as per requirements, or we just filter those that *could* accommodate if booking enough rooms. 
                // The prompt says "where max_occupancy >= adults". So let's follow that strictly.)
                $query->where('max_occupancy', '>=', $dto->adults);
            }])
            ->get();

        $results = new Collection();

        foreach ($hotels as $hotel) {
            foreach ($hotel->roomTypes as $roomType) {
                // Calculate actual availability inside the date range
                $availableRooms = $roomType->availableRooms($dto->checkIn, $dto->checkOut);

                if ($availableRooms === 0) {
                    continue;
                }

                // Calculate price using PricingService
                $totalPrice = $this->pricingService->calculate(
                    basePrice: $roomType->base_price,
                    checkIn: $dto->checkIn,
                    checkOut: $dto->checkOut,
                    roomsCount: 1 // Default to 1 room for search display purposes
                );

                $results->push([
                    'hotel'           => $hotel,
                    'room_type'       => $roomType,
                    'available_rooms' => $availableRooms,
                    'nights'          => $dto->nights(),
                    'total_price'     => $totalPrice,
                ]);
            }
        }

        return $results->sortBy('total_price')->values();
    }
}
