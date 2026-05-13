<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpaceRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Spatie handles the permission in the route, so we just return true here.
        return true; 
    }

    public function rules(): array
    {
        return [
            // Location Data
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],

            // Space Registration Data
            'name' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'size' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }
}