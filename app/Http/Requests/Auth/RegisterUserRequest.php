<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterUserRequest extends FormRequest {

    public function authorize(): bool {
        return true;
    }

    protected function prepareForValidation(): void {
        $this->merge([
            'email' => strtolower($this->email),
            'phone' => ltrim($this->phone, '0'), 
        ]);
    }

    public function rules(): array {
        return [
            // User Details
            'name'=> ['required', 'string', 'max:255'],
            'username'=> ['required', 'string', 'max:255', 'unique:users,username', 'alpha_num'],
            'email'=> ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],

            // Contact
            'phone_code'=> ['nullable', 'string', 'max:5'],
            'phone'=> ['nullable', 'string', 'max:20'],

            // Verification Assets
            'ktp' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'selfie_ktp' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
}