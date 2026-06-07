<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleRentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->rentRequest->space->owner_id === $this->user()->id;
    }

    public function rules(): array
    {
        $startDate = $this->rentRequest->start_date;

        return [
            'new_visit_date' => ['required', 'date', 'after_or_equal:today', 'before:' . $startDate],
            'response_note'  => ['required', 'string', 'max:1000'],
        ];
    }
}