<?php

namespace App\Services\Contracts;

use Illuminate\Support\Carbon;

interface PricingServiceInterface
{
    /**
     * Calculate total price based on rules.
     */
    public function calculate(float $basePrice, Carbon $checkIn, Carbon $checkOut, int $roomsCount = 1): float;

    /**
     * Provide a detailed breakdown of the pricing.
     */
    public function breakdown(float $basePrice, Carbon $checkIn, Carbon $checkOut, int $roomsCount = 1): array;
}
