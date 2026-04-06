<?php

namespace App\Services\Pricing\Rules;

use Illuminate\Support\Carbon;

interface PricingRuleInterface
{
    /**
     * Apply the rule to the current nightly price or total.
     * 
     * @param float $price The price to be modified.
     * @param Carbon $date The date for which the price is being calculated.
     * @param int $nightsCount total stay length.
     * @return float
     */
    public function apply(float $price, Carbon $date, int $nightsCount): float;

    /**
     * Determine if this rule applies at the nightly level.
     */
    public function isNightly(): bool;

    /**
     * Determine if this rule applies to the final total.
     */
    public function isFinal(): bool;
}
