<?php

namespace App\Services;

use App\DTOs\CreateBookingDTO;
use App\Enums\BookingStatus;
use App\Enums\HotelStatus;
use App\Exceptions\InsufficientRoomsException;
use App\Exceptions\OccupancyExceededException;
use App\Jobs\SendBookingConfirmationJob;
use App\Models\Booking;
use App\Models\Hotel;
use App\Services\Contracts\BookingServiceInterface;
use App\Services\Contracts\PricingServiceInterface;
use Illuminate\Support\Facades\DB;

class BookingService implements BookingServiceInterface
{
    public function __construct(
        private readonly PricingServiceInterface $pricingService
    ) {}


    public function book(CreateBookingDTO $dto): Booking
    {
        return DB::transaction(function () use ($dto) {
            // 2. Lock the room type for update
            $roomType = RoomType::lockForUpdate()->findOrFail($dto->roomTypeId);

            $hotel = Hotel::findOrFail($dto->hotelId);

            // 3. Validate hotel is active
            if ($hotel->status !== HotelStatus::ACTIVE) {
                throw new \InvalidArgumentException('Cannot book an inactive hotel.');
            }

            // 4. Validate adults_count <= max_occupancy * rooms_count
            $maxAllowedGuests = $roomType->max_occupancy * $dto->roomsCount;
            if ($dto->adultsCount > $maxAllowedGuests) {
                throw new OccupancyExceededException("Maximum occupancy exceeded. Allowed: $maxAllowedGuests, Requested: {$dto->adultsCount}");
            }

            // 5. Call availableRooms() INSIDE the lock
            $available = $roomType->availableRooms($dto->checkIn, $dto->checkOut);

            // 6. If available < requested -> throw InsufficientRoomsException
            if ($available < $dto->roomsCount) {
                throw new InsufficientRoomsException("Only $available rooms available for these dates.");
            }

            // 7. Calculate price via PricingService
            $totalPrice = $this->pricingService->calculate(
                basePrice: $roomType->base_price,
                checkIn: $dto->checkIn,
                checkOut: $dto->checkOut,
                roomsCount: $dto->roomsCount
            );

            // 8. Create booking
            $booking = Booking::create([
                'hotel_id'     => $dto->hotelId,
                'room_type_id' => $dto->roomTypeId,
                'user_id'      => $dto->userId,
                'guest_name'   => $dto->guestName,
                'guest_email'  => $dto->guestEmail,
                'check_in'     => $dto->checkIn,
                'check_out'    => $dto->checkOut,
                'rooms_count'  => $dto->roomsCount,
                'adults_count' => $dto->adultsCount,
                'total_price'  => $totalPrice,
                'status'       => BookingStatus::PENDING, 
            ]);

            // Dispatch confirmation job
            SendBookingConfirmationJob::dispatch($booking);

            \Illuminate\Support\Facades\Log::info("Booking created successfully", [
                'booking_id' => $booking->id,
                'user_id'    => $booking->user_id,
                'hotel_id'   => $booking->hotel_id,
                'total_price'=> $booking->total_price,
            ]);

            return $booking;
        });
    }

    public function cancel(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            // Lock the booking row for update to prevent concurrent cancellation
            $booking = Booking::lockForUpdate()->findOrFail($booking->id);

            if ($booking->status === BookingStatus::CANCELLED) {
                return $booking;
            }

            $oldStatus = $booking->status->value;
            $booking->update(['status' => BookingStatus::CANCELLED]);

            \Illuminate\Support\Facades\Log::info("Booking cancelled successfully", [
                'booking_id' => $booking->id,
                'user_id'    => $booking->user_id,
                'old_status' => $oldStatus,
            ]);

            return $booking;
        });
    }
}
