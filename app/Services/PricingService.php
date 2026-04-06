<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class PricingService
{
    private const WEEKEND_SURCHARGE_MULTIPLIER = 1.20; // +20%
    private const LONG_STAY_DISCOUNT_MULTIPLIER = 0.90; // -10%
    private const LONG_STAY_MIN_NIGHTS = 5;

    /**
     * Calculate total price based on rules.
     */
    public function calculate(float $basePrice, Carbon $checkIn, Carbon $checkOut, int $roomsCount = 1): float
    {
        $breakdown = $this->breakdown($basePrice, $checkIn, $checkOut, $roomsCount);
        return $breakdown['total_price'];
    }

    /**
     * Provide a detailed breakdown of the pricing.
     */
    public function breakdown(float $basePrice, Carbon $checkIn, Carbon $checkOut, int $roomsCount = 1): array
    {
        $nightsCount = $checkIn->diffInDays($checkOut);
        
        $nightlyDetails = [];
        $sumNightly = 0.0;

        $currentDate = $checkIn->copy();
        
        // 1. Loop each night individually
        for ($i = 0; $i < $nightsCount; $i++) {
            $nightPrice = $basePrice;

            // 2. If night falls on Friday or Saturday -> apply +20% surcharge
            if ($currentDate->isFriday() || $currentDate->isSaturday()) {
                $nightPrice *= self::WEEKEND_SURCHARGE_MULTIPLIER;
            }

            $nightlyDetails[] = [
                'date' => $currentDate->toDateString(),
                'price' => round($nightPrice, 2),
            ];

            // 3. Sum all nightly prices
            $sumNightly += $nightPrice;

            $currentDate->addDay();
        }

        $totalBeforeDiscount = $sumNightly;
        $totalAfterDiscount = $totalBeforeDiscount;
        $discountAmount = 0.0;

        // 4. If total nights >= 5 -> apply -10% discount on the total
        if ($nightsCount >= self::LONG_STAY_MIN_NIGHTS) {
            $totalAfterDiscount = $totalBeforeDiscount * self::LONG_STAY_DISCOUNT_MULTIPLIER;
            $discountAmount = $totalBeforeDiscount - $totalAfterDiscount;
        }

        // 5. Multiply by rooms_count
        $finalTotal = $totalAfterDiscount * $roomsCount;

        return [
            'base_price'      => $basePrice,
            'nights_count'    => $nightsCount,
            'rooms_count'     => $roomsCount,
            'nightly_details' => $nightlyDetails,
            'subtotal'        => round($totalBeforeDiscount * $roomsCount, 2),
            'discount_amount' => round($discountAmount * $roomsCount, 2),
            'total_price'     => round($finalTotal, 2),
        ];
    }
}
