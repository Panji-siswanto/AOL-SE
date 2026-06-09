<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleRentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $rentRequest = $this->route('rentRequest');
        if (!$rentRequest) return false;

        return $rentRequest->space->owner_id === $this->user()->id || $rentRequest->renter_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'new_visit_date' => ['nullable', 'date', 'before:new_start_date'],
            'new_start_date' => ['required', 'date', 'after_or_equal:today'],
            'new_end_date'   => ['required', 'date', 'after:new_start_date'],
            'response_note'  => ['nullable', 'string', 'max:1000'], 
        ];
    }

    public function messages()
    {
        return [
            'new_visit_date.before' => 'The visit date must be before the contract start date.',
        ];
    }
}