<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'hotel'        => new HotelResource($this->whenLoaded('hotel')),
            'room_type'    => new RoomTypeResource($this->whenLoaded('roomType')),
            'guest_name'   => $this->guest_name,
            'guest_email'  => $this->guest_email,
            'check_in'     => $this->check_in->toDateString(),
            'check_out'    => $this->check_out->toDateString(),
            'rooms_count'  => $this->rooms_count,
            'adults_count' => $this->adults_count,
            'total_price'  => $this->total_price,
            'status'       => $this->status->value,
            'created_at'   => $this->created_at->toIso8601String(),
        ];
    }
}
