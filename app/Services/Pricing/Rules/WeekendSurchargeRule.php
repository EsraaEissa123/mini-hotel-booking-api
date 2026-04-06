<?php

namespace App\Services\Pricing\Rules;

use Illuminate\Support\Carbon;

class WeekendSurchargeRule implements PricingRuleInterface
{
    private const SURCHARGE_MULTIPLIER = 1.20; // +20%

    public function apply(float $price, Carbon $date, int $nightsCount): float
    {
        if ($date->isFriday() || $date->isSaturday()) {
            return $price * self::SURCHARGE_MULTIPLIER;
        }

        return $price;
    }

    public function isNightly(): bool
    {
        return true;
    }

    public function isFinal(): bool
    {
        return false;
    }
}
