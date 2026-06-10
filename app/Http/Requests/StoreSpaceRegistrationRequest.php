<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpaceRegistrationRequest extends FormRequest
{
    public function authorize(): bool{
        return true; 
    }

    public function rules(): array{
        return [
            // Location
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],

            // Core Info
            'name' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],

            // Dimension Logic
            'dimension_type' => ['required', 'in:exact,total'],
            'length' => ['nullable', 'numeric', 'min:0.1', 'required_if:dimension_type,exact'],
            'width'  => ['nullable', 'numeric', 'min:0.1', 'required_if:dimension_type,exact'],
            'area'   => ['nullable', 'numeric', 'min:0.1', 'required_if:dimension_type,total'],
        

            // Pricing
            'pricing' => ['required', 'array', 'min:1'],
            'pricing.*.is_active' => ['nullable', 'boolean'],
            'pricing.*.price' => ['nullable', 'numeric', 'min:0', 'required_with:pricing.*.is_active'],

            // Documents & Photos
            'surat_tanah' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'surat_izin' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'primary_photo_index' => ['required', 'integer', 'min:0'],
            'photos' => ['required', 'array', 'min:1'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'photo_descriptions' => ['nullable', 'array'],
            'photo_descriptions.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array{
        return [
            'pricing.required' => 'You must select at least one rental rate.',
            'pricing.*.price.required_with' => 'Please enter a price for the selected rental rate.',
            'photos.required' => 'At least one property photo is required.',
        ];
    }
}