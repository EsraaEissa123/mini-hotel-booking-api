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
        $hotels = Cache::remember('hotels.all', 3600, function () {
            return Hotel::all();
        });

        return HotelResource::collection($hotels);
    }

    public function store(StoreHotelRequest $request): HotelResource
    {
        $hotel = Hotel::create($request->validated());

        Cache::forget('hotels.all');

        return new HotelResource($hotel);
    }

    public function show(Hotel $hotel): HotelResource
    {
        return new HotelResource($hotel);
    }

    public function update(UpdateHotelRequest $request, Hotel $hotel): HotelResource
    {
        $hotel->update($request->validated());

        Cache::forget('hotels.all');

        return new HotelResource($hotel);
    }

    public function destroy(Hotel $hotel): Response
    {
        $hotel->delete();

        Cache::forget('hotels.all');

        return response()->noContent();
    }
}
