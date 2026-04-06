<?php

namespace App\Services;

use App\Services\Contracts\PricingServiceInterface;
use App\Services\Pricing\Rules\PricingRuleInterface;
use Illuminate\Support\Carbon;

class PricingService implements PricingServiceInterface
{
    /**
     * The rules to be applied to the pricing.
     *
     * @var array<PricingRuleInterface>
     */
    private array $rules;

    /**
     * @param array<PricingRuleInterface>|null $rules Inject custom rules or use defaults.
     */
    public function __construct(?array $rules = null)
    {
        $this->rules = $rules ?? $this->defaultRules();
    }

    public function calculate(float $basePrice, Carbon $checkIn, Carbon $checkOut, int $roomsCount = 1): float
    {
        $breakdown = $this->breakdown($basePrice, $checkIn, $checkOut, $roomsCount);
        return $breakdown['total_price'];
    }

    public function breakdown(float $basePrice, Carbon $checkIn, Carbon $checkOut, int $roomsCount = 1): array
    {
        $nightsCount = $checkIn->diffInDays($checkOut);

        $nightlyDetails = [];
        $sumNightly = 0.0;

        $currentDate = $checkIn->copy();

        // 1. Process Nightly Rules
        for ($i = 0; $i < $nightsCount; $i++) {
            $nightPrice = $basePrice;

            foreach ($this->rules as $rule) {
                if ($rule->isNightly()) {
                    $nightPrice = $rule->apply($nightPrice, $currentDate, $nightsCount);
                }
            }

            $nightlyDetails[] = [
                'date' => $currentDate->toDateString(),
                'price' => round($nightPrice, 2),
            ];

            $sumNightly += $nightPrice;
            $currentDate->addDay();
        }

        // 2. Process Final Total Rules
        $totalBeforeDiscount = $sumNightly;
        $totalAfterDiscount = $totalBeforeDiscount;

        foreach ($this->rules as $rule) {
            if ($rule->isFinal()) {
                $totalAfterDiscount = $rule->apply($totalAfterDiscount, $checkIn, $nightsCount);
            }
        }

        $discountAmount = $totalBeforeDiscount - $totalAfterDiscount;
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

    /**
     * Default pricing rules applied when none are injected.
     *
     * @return array<PricingRuleInterface>
     */
    private function defaultRules(): array
    {
        return [
            new Pricing\Rules\WeekendSurchargeRule(),
            new Pricing\Rules\LongStayDiscountRule(),
        ];
    }
}
