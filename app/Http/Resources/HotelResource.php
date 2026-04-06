<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'city'    => $this->city,
            'address' => $this->address,
            'rating'  => $this->rating,
            'status'  => $this->status->value,
        ];
    }
}
