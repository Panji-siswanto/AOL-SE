<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectRentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->rentRequest->space->owner_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'reject_reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}