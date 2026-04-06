<?php

namespace App\Http\Controllers\Api;

use App\DTOs\AvailabilitySearchDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\AvailabilitySearchRequest;
use App\Http\Resources\AvailabilityResource;
use App\Services\Contracts\AvailabilityServiceInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AvailabilityController extends Controller
{
    public function __construct(
        private readonly AvailabilityServiceInterface $availabilityService
    ) {}


    public function index(AvailabilitySearchRequest $request): AnonymousResourceCollection
    {
        $dto = AvailabilitySearchDTO::fromArray($request->validated());

        $results = $this->availabilityService->search($dto);

        return AvailabilityResource::collection($results);
    }
}
