<?php

namespace App\Services\Contracts;

use App\DTOs\AvailabilitySearchDTO;
use Illuminate\Support\Collection;

interface AvailabilityServiceInterface
{
    /**
     * Search for available hotels and room types.
     */
    public function search(AvailabilitySearchDTO $dto): Collection;
}
