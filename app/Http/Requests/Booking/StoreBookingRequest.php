<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hotel_id'     => ['required', 'integer', 'exists:hotels,id'],
            'room_type_id' => [
                'required',
                'integer',
                Rule::exists('room_types', 'id')->where('hotel_id', $this->hotel_id),
            ],
            'guest_name'   => ['required', 'string', 'max:255'],
            'guest_email'  => ['required', 'email', 'max:255'],
            'check_in'     => ['required', 'date', 'after_or_equal:today'],
            'check_out'    => ['required', 'date', 'after:check_in'],
            'rooms_count'  => ['required', 'integer', 'min:1'],
            'adults_count' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'room_type_id.exists' => 'The selected room type does not belong to the specified hotel.',
        ];
    }
}
