<?php

namespace App\Http\Requests\RoomType;

use App\Enums\RoomName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['sometimes', Rule::enum(RoomName::class)],
            'max_occupancy' => ['sometimes', 'integer', 'min:1'],
            'base_price'    => ['sometimes', 'numeric', 'min:0'],
            'total_rooms'   => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
