<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Always true for guest registration
        return true; 
    }

    /**
     * Prepare and sanitize data before validation runs.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Force email to lowercase before checking if it's unique
            'email' => strtolower($this->email),
            // Strip the leading zero from the phone number
            'phone' => ltrim($this->phone, '0'), 
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username', 'alpha_num'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'phone_code' => ['nullable', 'string', 'max:5'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'ktp' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'selfie_ktp' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
}