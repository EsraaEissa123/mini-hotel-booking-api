<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'guest_name'   => ['required', 'string', 'max:255'],
            'guest_email'  => ['required', 'email', 'max:255'],
            'check_in'     => ['required', 'date', 'after_or_equal:today'],
            'check_out'    => ['required', 'date', 'after:check_in'],
            'rooms_count'  => ['required', 'integer', 'min:1'],
            'adults_count' => ['required', 'integer', 'min:1'],
        ];
    }
}
