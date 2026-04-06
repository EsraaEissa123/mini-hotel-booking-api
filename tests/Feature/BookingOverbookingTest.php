<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\HotelStatus;
use App\Enums\RoomName;
use App\Exceptions\InsufficientRoomsException;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookingOverbookingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Hotel $hotel;
    private RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->hotel = Hotel::create([
            'name' => 'Test Hotel',
            'city' => 'Test City',
            'address' => 'Test Address',
            'rating' => 4,
            'status' => HotelStatus::ACTIVE,
        ]);

        $this->roomType = $this->hotel->roomTypes()->create([
            'name' => RoomName::SINGLE,
            'max_occupancy' => 2,
            'base_price' => 100.0,
            'total_rooms' => 1, // Only 1 room available!
        ]);
    }

    public function test_successful_booking()
    {
        $response = $this->actingAs($this->user)->postJson('/api/bookings', [
            'hotel_id' => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'rooms_count' => 1,
            'adults_count' => 1,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->user->id,
            'hotel_id' => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
        ]);
    }

    public function test_second_booking_on_same_room_throws_exception()
    {
        // First booking takes the only room
        Booking::create([
            'hotel_id' => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'user_id' => $this->user->id,
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'rooms_count' => 1,
            'adults_count' => 1,
            'total_price' => 200.0,
            'status' => BookingStatus::PENDING,
        ]);

        // Attempt second booking for overlapping dates
        $response = $this->actingAs($this->user)->postJson('/api/bookings', [
            'hotel_id' => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'guest_name' => 'Jane Doe',
            'guest_email' => 'jane@example.com',
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'rooms_count' => 1,
            'adults_count' => 1,
        ]);

        // Should receive 409 Conflict as configured in bootstrap/app.php
        $response->assertStatus(409);
        $response->assertJsonFragment([
            'message' => 'Only 0 rooms available for these dates.'
        ]);
    }

    public function test_booking_works_again_after_cancellation()
    {
        // First booking takes the only room
        $booking = Booking::create([
            'hotel_id' => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'user_id' => $this->user->id,
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'rooms_count' => 1,
            'adults_count' => 1,
            'total_price' => 200.0,
            'status' => BookingStatus::PENDING,
        ]);

        // Cancel it
        $this->actingAs($this->user)->patchJson("/api/bookings/{$booking->id}/cancel")->assertStatus(200);

        // Attempt second booking for same dates
        $response = $this->actingAs($this->user)->postJson('/api/bookings', [
            'hotel_id' => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'guest_name' => 'Jane Doe',
            'guest_email' => 'jane@example.com',
            'check_in' => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'rooms_count' => 1,
            'adults_count' => 1,
        ]);

        // Should succeed this time
        $response->assertStatus(201);
    }
}
