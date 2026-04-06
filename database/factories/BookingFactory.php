<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/** @extends Factory<Booking> */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $checkIn = Carbon::tomorrow()->addDays(fake()->numberBetween(1, 30));
        $checkOut = $checkIn->copy()->addDays(fake()->numberBetween(1, 7));

        return [
            'hotel_id'     => Hotel::factory(),
            'room_type_id' => RoomType::factory(),
            'user_id'      => User::factory(),
            'guest_name'   => fake()->name(),
            'guest_email'  => fake()->safeEmail(),
            'check_in'     => $checkIn->toDateString(),
            'check_out'    => $checkOut->toDateString(),
            'rooms_count'  => 1,
            'adults_count' => fake()->numberBetween(1, 2),
            'total_price'  => fake()->randomFloat(2, 100, 2000),
            'status'       => BookingStatus::PENDING,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => BookingStatus::CONFIRMED]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => BookingStatus::CANCELLED]);
    }
}
