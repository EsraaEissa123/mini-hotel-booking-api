<?php

namespace App\Http\Requests\Hotel;

use App\Enums\HotelStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:255'],
            'city'    => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'status'  => ['required', Rule::enum(HotelStatus::class)],
        ];
    }
}
