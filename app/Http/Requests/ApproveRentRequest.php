<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveRentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->rentRequest->space->owner_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'response_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}