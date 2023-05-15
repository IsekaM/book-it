<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            "first_name" => ["required"],
            "last_name" => ["required"],
            "email" => [
                "required",
                "email",
                "max:254",
                "unique:App\Models\User,email",
            ],
            "password" => [
                "required",
                Password::min(8)
                    ->numbers()
                    ->mixedCase()
                    ->symbols()
                    ->uncompromised(),
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
