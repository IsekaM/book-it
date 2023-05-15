<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            "email" => "email|required|exists:App\Models\User,email",
            "password" => "required",
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = User::firstWhere("email", $this->get("email"));

            if (
                $user &&
                !Hash::check($this->get("password"), $user->password)
            ) {
                $validator
                    ->errors()
                    ->add(
                        "email",
                        "These credentials do not match our records.",
                    );
            }
        });
    }
}
