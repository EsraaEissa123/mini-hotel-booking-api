<?php

namespace Database\Factories;

use App\Enums\RoomName;
use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RoomType> */
class RoomTypeFactory extends Factory
{
    protected $model = RoomType::class;

    public function definition(): array
    {
        return [
            'hotel_id'      => Hotel::factory(),
            'name'          => fake()->randomElement(RoomName::cases()),
            'max_occupancy' => fake()->numberBetween(1, 4),
            'base_price'    => fake()->randomFloat(2, 50, 500),
            'total_rooms'   => fake()->numberBetween(1, 30),
        ];
    }

    public function single(): static
    {
        return $this->state(fn () => [
            'name'          => RoomName::SINGLE,
            'max_occupancy' => 1,
        ]);
    }

    public function double(): static
    {
        return $this->state(fn () => [
            'name'          => RoomName::DOUBLE,
            'max_occupancy' => 2,
        ]);
    }

    public function suite(): static
    {
        return $this->state(fn () => [
            'name'          => RoomName::SUITE,
            'max_occupancy' => 4,
        ]);
    }
}
