<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pricing_id' => ['required', 'exists:space_registration_prices,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'duration'   => ['required', 'integer', 'min:1'],
            'visit_date' => ['required', 'date', 'after_or_equal:today', 'before:start_date'],
            'note'       => ['nullable', 'string', 'max:2000'],
        ];
    }
}