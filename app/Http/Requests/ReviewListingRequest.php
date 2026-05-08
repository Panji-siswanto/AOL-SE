<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReviewListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth handled by Spatie middleware in web.php
}

    public function rules(): array
    {
        return [
            'note' => ['required', 'string', 'max:1000'], 
        ];
    }
}