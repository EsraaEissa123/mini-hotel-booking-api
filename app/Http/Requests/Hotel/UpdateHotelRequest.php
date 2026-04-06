<?php

namespace App\Http\Requests\Hotel;

use App\Enums\HotelStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => ['sometimes', 'string', 'max:255'],
            'city'    => ['sometimes', 'string', 'max:255'],
            'address' => ['sometimes', 'string', 'max:500'],
            'rating'  => ['sometimes', 'integer', 'min:1', 'max:5'],
            'status'  => ['sometimes', Rule::enum(HotelStatus::class)],
        ];
    }
}
