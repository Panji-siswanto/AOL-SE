<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpaceRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            // Location Data
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],

            // Space Registration Data
            'name' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'size' => ['required', 'string', 'max:50'],

            // Dynamic Pricing Validation
            'pricing' => ['required', 'array', 'min:1'],
            'pricing.*.is_active' => ['nullable', 'boolean'],
            // Only require the price amount if the specific pricing type was toggled active
            'pricing.*.price' => ['nullable', 'numeric', 'min:0', 'required_with:pricing.*.is_active'],

            // Legal Assets Validation
            'surat_tanah' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // Max 5MB
            'surat_izin' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            // Gallery Validation
            'primary_photo_index' => ['required', 'integer', 'min:0'],
            'photos' => ['required', 'array', 'min:1'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'photo_descriptions' => ['nullable', 'array'],
            'photo_descriptions.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'pricing.required' => 'You must select at least one rental rate.',
            'pricing.*.price.required_with' => 'Please enter a price for the selected rental rate.',
            'photos.required' => 'At least one property photo is required.',
        ];
    }
}