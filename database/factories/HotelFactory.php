<?php

namespace Database\Factories;

use App\Enums\HotelStatus;
use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Hotel> */
class HotelFactory extends Factory
{
    protected $model = Hotel::class;

    public function definition(): array
    {
        return [
            'name'    => fake()->company() . ' Hotel',
            'city'    => fake()->city(),
            'address' => fake()->streetAddress(),
            'rating'  => fake()->numberBetween(1, 5),
            'status'  => HotelStatus::ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => HotelStatus::INACTIVE]);
    }
}
