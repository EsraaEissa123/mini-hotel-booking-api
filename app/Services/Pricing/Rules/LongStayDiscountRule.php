<?php

namespace App\Services\Pricing\Rules;

use Illuminate\Support\Carbon;

class LongStayDiscountRule implements PricingRuleInterface
{
    private const MIN_NIGHTS = 5;
    private const DISCOUNT_MULTIPLIER = 0.90; // -10%

    public function apply(float $price, Carbon $date, int $nightsCount): float
    {
        if ($nightsCount >= self::MIN_NIGHTS) {
            return $price * self::DISCOUNT_MULTIPLIER;
        }

        return $price;
    }

    public function isNightly(): bool
    {
        return false;
    }

    public function isFinal(): bool
    {
        return true;
    }
}
