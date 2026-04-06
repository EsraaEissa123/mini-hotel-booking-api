<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hotel\StoreHotelRequest;
use App\Http\Requests\Hotel\UpdateHotelRequest;
use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class HotelController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return HotelResource::collection(Hotel::all());
    }

    public function store(StoreHotelRequest $request): HotelResource
    {
        $hotel = Hotel::create($request->validated());

        return new HotelResource($hotel);
    }

    public function show(Hotel $hotel): HotelResource
    {
        return new HotelResource($hotel);
    }

    public function update(UpdateHotelRequest $request, Hotel $hotel): HotelResource
    {
        $hotel->update($request->validated());

        return new HotelResource($hotel);
    }

    public function destroy(Hotel $hotel): Response
    {
        $hotel->delete();

        return response()->noContent();
    }
}
