<?php

namespace Tests\Unit;

use App\Services\PricingService;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class PricingServiceTest extends TestCase
{
    private PricingService $pricingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricingService = new PricingService();
    }

    public function test_weekday_night_is_base_price_only()
    {
        // A Monday
        $checkIn = Carbon::parse('2025-05-05');
        $checkOut = Carbon::parse('2025-05-06');
        
        $price = $this->pricingService->calculate(100.0, $checkIn, $checkOut);

        // 1 night * 100
        $this->assertEquals(100.0, $price);
    }

    public function test_friday_saturday_have_20_percent_surcharge()
    {
        // Friday to Sunday (2 nights: Friday night, Saturday night)
        $checkIn = Carbon::parse('2025-05-09');
        $checkOut = Carbon::parse('2025-05-11');
        
        $price = $this->pricingService->calculate(100.0, $checkIn, $checkOut);

        // 2 nights * (100 * 1.20) = 240
        $this->assertEquals(240.0, $price);
    }

    public function test_five_or_more_nights_have_10_percent_discount()
    {
        // Monday to Saturday (5 nights: Mon, Tue, Wed, Thu, Fri)
        $checkIn = Carbon::parse('2025-05-05');
        $checkOut = Carbon::parse('2025-05-10');
        
        $price = $this->pricingService->calculate(100.0, $checkIn, $checkOut);

        // Mon=100, Tue=100, Wed=100, Thu=100, Fri=120 -> Subtotal = 520
        // Discount 10% -> 520 * 0.90 = 468
        $this->assertEquals(468.0, $price);
    }

    public function test_rooms_count_multiplier_works()
    {
        // A Monday
        $checkIn = Carbon::parse('2025-05-05');
        $checkOut = Carbon::parse('2025-05-06');
        
        // 3 rooms
        $price = $this->pricingService->calculate(100.0, $checkIn, $checkOut, 3);

        // 1 night * 100 * 3 rooms
        $this->assertEquals(300.0, $price);
    }
}
