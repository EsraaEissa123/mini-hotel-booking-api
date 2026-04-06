<?php

namespace Tests\Feature;

use App\Enums\HotelStatus;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotelCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_hotels_without_auth(): void
    {
        Hotel::factory()->count(3)->create();
        Hotel::factory()->inactive()->create(); // should not appear

        $response = $this->getJson('/api/hotels');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_can_show_single_hotel_without_auth(): void
    {
        $hotel = Hotel::factory()->create();

        $response = $this->getJson("/api/hotels/{$hotel->id}");

        $response->assertOk()
            ->assertJsonFragment(['name' => $hotel->name]);
    }

    public function test_authenticated_user_can_create_hotel(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/hotels', [
            'name'    => 'New Hotel',
            'city'    => 'Cairo',
            'address' => '123 Test Street',
            'rating'  => 5,
            'status'  => 'active',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'New Hotel']);

        $this->assertDatabaseHas('hotels', ['name' => 'New Hotel']);
    }

    public function test_unauthenticated_user_cannot_create_hotel(): void
    {
        $response = $this->postJson('/api/hotels', [
            'name'    => 'Unauthorized',
            'city'    => 'Cairo',
            'address' => '123 Test Street',
            'rating'  => 5,
            'status'  => 'active',
        ]);

        $response->assertStatus(401);
    }

    public function test_store_hotel_fails_with_invalid_data(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/hotels', [
            'name'   => '',  // required
            'rating' => 10,  // max 5
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'city', 'address', 'rating', 'status']);
    }

    public function test_authenticated_user_can_update_hotel(): void
    {
        $hotel = Hotel::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->user)->putJson("/api/hotels/{$hotel->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertOk()
            ->assertJsonFragment(['name' => 'Updated Name']);
    }

    public function test_unauthenticated_user_cannot_update_hotel(): void
    {
        $hotel = Hotel::factory()->create();

        $response = $this->putJson("/api/hotels/{$hotel->id}", [
            'name' => 'Hacked',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_delete_hotel(): void
    {
        $hotel = Hotel::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/hotels/{$hotel->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('hotels', ['id' => $hotel->id]);
    }

    public function test_unauthenticated_user_cannot_delete_hotel(): void
    {
        $hotel = Hotel::factory()->create();

        $response = $this->deleteJson("/api/hotels/{$hotel->id}");

        $response->assertStatus(401);
    }

    public function test_show_nonexistent_hotel_returns_404(): void
    {
        $response = $this->getJson('/api/hotels/9999');

        $response->assertStatus(404);
    }
}
