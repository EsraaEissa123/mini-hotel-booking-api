<?php

namespace App\Http\Requests\RoomType;

use App\Enums\RoomName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoomTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', Rule::enum(RoomName::class)],
            'max_occupancy'  => ['required', 'integer', 'min:1'],
            'base_price'     => ['required', 'numeric', 'min:0'],
            'total_rooms'    => ['required', 'integer', 'min:1'],
        ];
    }
}
