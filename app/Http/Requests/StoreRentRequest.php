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
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'duration'   => ['required', 'integer', 'min:1'],
            'visit_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:start_date'],
            // 'end_date' => ['required', 'date', 'after:start_date'],
            'note'       => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages()
    {
        return [
            'visit_date.before_or_equal' => 'The visit date must be on or before the contract start date.',
        ];
    }
}