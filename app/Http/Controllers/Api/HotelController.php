<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hotel\StoreHotelRequest;
use App\Http\Requests\Hotel\UpdateHotelRequest;
use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class HotelController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $hotels = Cache::remember('hotels.page.' . request('page', 1), 3600, function () {
            return Hotel::active()->paginate(15);
        });

        return HotelResource::collection($hotels);
    }

    public function store(StoreHotelRequest $request): HotelResource
    {
        $hotel = Hotel::create($request->validated());

        $this->clearCache();

        return new HotelResource($hotel);
    }

    public function show(Hotel $hotel): HotelResource
    {
        return new HotelResource($hotel);
    }

    public function update(UpdateHotelRequest $request, Hotel $hotel): HotelResource
    {
        $hotel->update($request->validated());

        $this->clearCache();

        return new HotelResource($hotel);
    }

    public function destroy(Hotel $hotel): Response
    {
        $hotel->delete();

        $this->clearCache();

        return response()->noContent();
    }

    /**
     * Clear all hotel-related cache entries.
     */
    private function clearCache(): void
    {
        // Clear paginated cache using pattern-based approach
        Cache::flush(); // In production, use tagged caching: Cache::tags(['hotels'])->flush()
    }
}
