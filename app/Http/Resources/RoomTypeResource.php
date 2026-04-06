<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'hotel_id'       => $this->hotel_id,
            'name'           => $this->name->value,
            'max_occupancy'  => $this->max_occupancy,
            'base_price'     => $this->base_price,
            'total_rooms'    => $this->total_rooms,
        ];
    }
}
