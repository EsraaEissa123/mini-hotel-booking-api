<?php

namespace Database\Seeders;

use App\Enums\HotelStatus;
use App\Enums\RoomName;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1 test user: test@example.com / password
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // 3 active hotels in different cities (Cairo, Alexandria, Luxor)
        $cairoHotel = Hotel::create([
            'name' => 'Grand Cairo Resort',
            'city' => 'Cairo',
            'address' => '123 Nile Corniche',
            'rating' => 5,
            'status' => HotelStatus::ACTIVE,
        ]);

        $alexHotel = Hotel::create([
            'name' => 'Mediterranean Vista',
            'city' => 'Alexandria',
            'address' => '45 Seafront Blvd',
            'rating' => 4,
            'status' => HotelStatus::ACTIVE,
        ]);

        $luxorHotel = Hotel::create([
            'name' => 'Pharaohs Palace',
            'city' => 'Luxor',
            'address' => 'Kings Valley Road',
            'rating' => 5,
            'status' => HotelStatus::ACTIVE,
        ]);

        // 1 inactive hotel (to prove filter works)
        Hotel::create([
            'name' => 'Old Desert Inn',
            'city' => 'Aswan',
            'address' => 'Sand Dune Lane 1',
            'rating' => 3,
            'status' => HotelStatus::INACTIVE,
        ]);

        // Each hotel has 2-3 room types with varied prices and capacities
        
        // Cairo Hotel Rooms
        $cairoHotel->roomTypes()->create([
            'name' => RoomName::SINGLE,
            'max_occupancy' => 1,
            'base_price' => 100.00,
            'total_rooms' => 10,
        ]);
        $cairoHotel->roomTypes()->create([
            'name' => RoomName::DOUBLE,
            'max_occupancy' => 2,
            'base_price' => 150.00,
            'total_rooms' => 15,
        ]);
        $cairoHotel->roomTypes()->create([
            'name' => RoomName::SUITE,
            'max_occupancy' => 4,
            'base_price' => 350.00,
            'total_rooms' => 5,
        ]);

        // Alex Hotel Rooms
        $alexHotel->roomTypes()->create([
            'name' => RoomName::DOUBLE,
            'max_occupancy' => 2,
            'base_price' => 120.00,
            'total_rooms' => 20,
        ]);
        $alexHotel->roomTypes()->create([
            'name' => RoomName::SUITE,
            'max_occupancy' => 3,
            'base_price' => 250.00,
            'total_rooms' => 8,
        ]);

        // Luxor Hotel Rooms
        $luxorHotel->roomTypes()->create([
            'name' => RoomName::SINGLE,
            'max_occupancy' => 1,
            'base_price' => 80.00,
            'total_rooms' => 12,
        ]);
        $luxorHotel->roomTypes()->create([
            'name' => RoomName::DOUBLE,
            'max_occupancy' => 2,
            'base_price' => 140.00,
            'total_rooms' => 10,
        ]);
    }
}
