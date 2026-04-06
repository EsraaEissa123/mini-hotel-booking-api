<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\HotelStatus;
use App\Enums\RoomName;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookingValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Hotel $hotel;
    private RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->hotel = Hotel::factory()->create([
            'status' => HotelStatus::ACTIVE,
        ]);

        $this->roomType = $this->hotel->roomTypes()->create([
            'name'          => RoomName::DOUBLE,
            'max_occupancy' => 2,
            'base_price'    => 100.00,
            'total_rooms'   => 5,
        ]);
    }

    public function test_unauthenticated_user_cannot_create_booking(): void
    {
        $response = $this->postJson('/api/bookings', [
            'hotel_id'     => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'guest_name'   => 'John Doe',
            'guest_email'  => 'john@example.com',
            'check_in'     => Carbon::tomorrow()->toDateString(),
            'check_out'    => Carbon::tomorrow()->addDays(2)->toDateString(),
            'rooms_count'  => 1,
            'adults_count' => 1,
        ]);

        $response->assertStatus(401);
    }

    public function test_booking_fails_with_missing_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/bookings', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'hotel_id', 'room_type_id', 'guest_name',
                'guest_email', 'check_in', 'check_out',
                'rooms_count', 'adults_count',
            ]);
    }

    public function test_booking_fails_with_nonexistent_hotel(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/bookings', [
            'hotel_id'     => 9999,
            'room_type_id' => $this->roomType->id,
            'guest_name'   => 'John Doe',
            'guest_email'  => 'john@example.com',
            'check_in'     => Carbon::tomorrow()->toDateString(),
            'check_out'    => Carbon::tomorrow()->addDays(2)->toDateString(),
            'rooms_count'  => 1,
            'adults_count' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['hotel_id']);
    }

    public function test_booking_fails_when_room_type_does_not_belong_to_hotel(): void
    {
        $otherHotel = Hotel::factory()->create();
        $otherRoomType = $otherHotel->roomTypes()->create([
            'name'          => RoomName::SINGLE,
            'max_occupancy' => 1,
            'base_price'    => 50.00,
            'total_rooms'   => 3,
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/bookings', [
            'hotel_id'     => $this->hotel->id,
            'room_type_id' => $otherRoomType->id, // Doesn't belong to this hotel!
            'guest_name'   => 'John Doe',
            'guest_email'  => 'john@example.com',
            'check_in'     => Carbon::tomorrow()->toDateString(),
            'check_out'    => Carbon::tomorrow()->addDays(2)->toDateString(),
            'rooms_count'  => 1,
            'adults_count' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['room_type_id']);
    }

    public function test_booking_fails_with_past_check_in_date(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/bookings', [
            'hotel_id'     => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'guest_name'   => 'John Doe',
            'guest_email'  => 'john@example.com',
            'check_in'     => Carbon::yesterday()->toDateString(),
            'check_out'    => Carbon::tomorrow()->toDateString(),
            'rooms_count'  => 1,
            'adults_count' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['check_in']);
    }

    public function test_booking_fails_when_occupancy_exceeded(): void
    {
        // Room type max_occupancy = 2, trying 3 adults in 1 room
        $response = $this->actingAs($this->user)->postJson('/api/bookings', [
            'hotel_id'     => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'guest_name'   => 'John Doe',
            'guest_email'  => 'john@example.com',
            'check_in'     => Carbon::tomorrow()->toDateString(),
            'check_out'    => Carbon::tomorrow()->addDays(2)->toDateString(),
            'rooms_count'  => 1,
            'adults_count' => 3,
        ]);

        $response->assertStatus(422);
    }

    public function test_user_cannot_view_another_users_booking(): void
    {
        $otherUser = User::factory()->create();

        $booking = Booking::create([
            'hotel_id'     => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'user_id'      => $otherUser->id,
            'guest_name'   => 'Other User',
            'guest_email'  => 'other@example.com',
            'check_in'     => Carbon::tomorrow()->toDateString(),
            'check_out'    => Carbon::tomorrow()->addDays(2)->toDateString(),
            'rooms_count'  => 1,
            'adults_count' => 1,
            'total_price'  => 200,
            'status'       => BookingStatus::PENDING,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/bookings/{$booking->id}");

        $response->assertStatus(403);
    }

    public function test_user_cannot_cancel_another_users_booking(): void
    {
        $otherUser = User::factory()->create();

        $booking = Booking::create([
            'hotel_id'     => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'user_id'      => $otherUser->id,
            'guest_name'   => 'Other User',
            'guest_email'  => 'other@example.com',
            'check_in'     => Carbon::tomorrow()->toDateString(),
            'check_out'    => Carbon::tomorrow()->addDays(2)->toDateString(),
            'rooms_count'  => 1,
            'adults_count' => 1,
            'total_price'  => 200,
            'status'       => BookingStatus::PENDING,
        ]);

        $response = $this->actingAs($this->user)->patchJson("/api/bookings/{$booking->id}/cancel");

        $response->assertStatus(403);
    }

    public function test_cancelling_already_cancelled_booking_returns_booking(): void
    {
        $booking = Booking::create([
            'hotel_id'     => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'user_id'      => $this->user->id,
            'guest_name'   => 'John Doe',
            'guest_email'  => 'john@example.com',
            'check_in'     => Carbon::tomorrow()->toDateString(),
            'check_out'    => Carbon::tomorrow()->addDays(2)->toDateString(),
            'rooms_count'  => 1,
            'adults_count' => 1,
            'total_price'  => 200,
            'status'       => BookingStatus::CANCELLED,
        ]);

        $response = $this->actingAs($this->user)->patchJson("/api/bookings/{$booking->id}/cancel");

        $response->assertOk()
            ->assertJsonFragment(['status' => 'cancelled']);
    }

    public function test_user_can_list_only_their_own_bookings(): void
    {
        $otherUser = User::factory()->create();

        // Create booking for current user
        Booking::create([
            'hotel_id'     => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'user_id'      => $this->user->id,
            'guest_name'   => 'My Booking',
            'guest_email'  => 'me@example.com',
            'check_in'     => Carbon::tomorrow()->toDateString(),
            'check_out'    => Carbon::tomorrow()->addDays(2)->toDateString(),
            'rooms_count'  => 1,
            'adults_count' => 1,
            'total_price'  => 200,
            'status'       => BookingStatus::PENDING,
        ]);

        // Create booking for OTHER user
        Booking::create([
            'hotel_id'     => $this->hotel->id,
            'room_type_id' => $this->roomType->id,
            'user_id'      => $otherUser->id,
            'guest_name'   => 'Other Booking',
            'guest_email'  => 'other@example.com',
            'check_in'     => Carbon::tomorrow()->toDateString(),
            'check_out'    => Carbon::tomorrow()->addDays(2)->toDateString(),
            'rooms_count'  => 1,
            'adults_count' => 1,
            'total_price'  => 200,
            'status'       => BookingStatus::PENDING,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/bookings');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('My Booking', $data[0]['guest_name']);
    }
}
