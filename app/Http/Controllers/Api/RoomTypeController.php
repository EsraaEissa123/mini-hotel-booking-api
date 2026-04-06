<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoomType\StoreRoomTypeRequest;
use App\Http\Requests\RoomType\UpdateRoomTypeRequest;
use App\Http\Resources\RoomTypeResource;
use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RoomTypeController extends Controller
{
    public function index(Hotel $hotel): AnonymousResourceCollection
    {
        return RoomTypeResource::collection($hotel->roomTypes);
    }

    public function store(StoreRoomTypeRequest $request, Hotel $hotel): RoomTypeResource
    {
        $roomType = $hotel->roomTypes()->create($request->validated());

        return new RoomTypeResource($roomType);
    }

    public function show(Hotel $hotel, RoomType $roomType): RoomTypeResource
    {
        return new RoomTypeResource($roomType);
    }

    public function update(UpdateRoomTypeRequest $request, Hotel $hotel, RoomType $roomType): RoomTypeResource
    {
        $roomType->update($request->validated());

        return new RoomTypeResource($roomType);
    }

    public function destroy(Hotel $hotel, RoomType $roomType): Response
    {
        $roomType->delete();

        return response()->noContent();
    }
}
