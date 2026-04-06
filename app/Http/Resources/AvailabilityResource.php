<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AvailabilityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'hotel' => [
                'id'      => $this['hotel']->id,
                'name'    => $this['hotel']->name,
                'city'    => $this['hotel']->city,
                'address' => $this['hotel']->address,
                'rating'  => $this['hotel']->rating,
            ],
            'room_type' => [
                'id'            => $this['room_type']->id,
                'name'          => $this['room_type']->name->value,
                'max_occupancy' => $this['room_type']->max_occupancy,
                'base_price'    => $this['room_type']->base_price,
            ],
            'available_rooms' => $this['available_rooms'],
            'nights'          => $this['nights'],
            'total_price'     => $this['total_price'],
        ];
    }
}
