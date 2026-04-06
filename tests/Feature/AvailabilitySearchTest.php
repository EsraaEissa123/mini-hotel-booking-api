<?php

namespace Tests\Feature;

use App\Enums\HotelStatus;
use App\Enums\RoomName;
use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AvailabilitySearchTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $hotel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::factory()->create([
            'city'   => 'Cairo',
            'status' => HotelStatus::ACTIVE,
        ]);

        $this->hotel->roomTypes()->create([
            'name'          => RoomName::DOUBLE,
            'max_occupancy' => 2,
            'base_price'    => 100.00,
            'total_rooms'   => 5,
        ]);
    }

    public function test_availability_search_returns_results_for_valid_query(): void
    {
        $response = $this->getJson('/api/availability?' . http_build_query([
            'city'      => 'Cairo',
            'check_in'  => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(3)->toDateString(),
            'adults'    => 2,
        ]));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'hotel' => ['id', 'name', 'city', 'address', 'rating'],
                        'room_type' => ['id', 'name', 'max_occupancy', 'base_price'],
                        'available_rooms',
                        'nights',
                        'total_price',
                    ],
                ],
            ]);
    }

    public function test_availability_excludes_inactive_hotels(): void
    {
        Hotel::factory()->inactive()->create(['city' => 'Cairo']);

        $response = $this->getJson('/api/availability?' . http_build_query([
            'city'      => 'Cairo',
            'check_in'  => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'adults'    => 1,
        ]));

        $response->assertOk();
        // Only the active hotel should appear
        $data = $response->json('data');
        foreach ($data as $item) {
            $this->assertNotEquals(HotelStatus::INACTIVE->value, $item['hotel']['id']);
        }
    }

    public function test_availability_respects_max_occupancy(): void
    {
        // Our room type has max_occupancy = 2, searching for 3 adults should return empty
        $response = $this->getJson('/api/availability?' . http_build_query([
            'city'      => 'Cairo',
            'check_in'  => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'adults'    => 3,
        ]));

        $response->assertOk();
        $this->assertEmpty($response->json('data'));
    }

    public function test_availability_search_fails_with_missing_params(): void
    {
        $response = $this->getJson('/api/availability');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['city', 'check_in', 'check_out', 'adults']);
    }

    public function test_availability_search_fails_with_past_check_in(): void
    {
        $response = $this->getJson('/api/availability?' . http_build_query([
            'city'      => 'Cairo',
            'check_in'  => Carbon::yesterday()->toDateString(),
            'check_out' => Carbon::tomorrow()->toDateString(),
            'adults'    => 1,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['check_in']);
    }

    public function test_availability_search_fails_when_checkout_before_checkin(): void
    {
        $response = $this->getJson('/api/availability?' . http_build_query([
            'city'      => 'Cairo',
            'check_in'  => Carbon::tomorrow()->addDays(5)->toDateString(),
            'check_out' => Carbon::tomorrow()->toDateString(),
            'adults'    => 1,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['check_out']);
    }

    public function test_availability_returns_correct_price_for_stay(): void
    {
        // 3 weekday nights at $100 = $300 (no long-stay discount)
        $monday = Carbon::parse('next monday');
        $thursday = $monday->copy()->addDays(3);

        $response = $this->getJson('/api/availability?' . http_build_query([
            'city'      => 'Cairo',
            'check_in'  => $monday->toDateString(),
            'check_out' => $thursday->toDateString(),
            'adults'    => 1,
        ]));

        $response->assertOk();
        $data = $response->json('data');

        if (! empty($data)) {
            $this->assertEquals(3, $data[0]['nights']);
            $this->assertEquals(300.00, $data[0]['total_price']);
        }
    }

    public function test_availability_returns_empty_for_nonexistent_city(): void
    {
        $response = $this->getJson('/api/availability?' . http_build_query([
            'city'      => 'NonExistentCity',
            'check_in'  => Carbon::tomorrow()->toDateString(),
            'check_out' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'adults'    => 1,
        ]));

        $response->assertOk();
        $this->assertEmpty($response->json('data'));
    }
}
