<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckoutRequest extends FormRequest
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
            "user_id" => ["required", "exists:App\Models\User,id"],
            "address_line_1" => ["string", "required"],
            "address_line_2" => ["string", "nullable"],
            "city" => ["string", "required"],
            "parish" => ["string", "required"],
            "phone_number" => ["string", "required"],
            "status" => ["string", Rule::in([OrderStatus::PROCESSING->name])],
            "payment_method" => [
                "string",
                Rule::in(PaymentMethod::CREDIT_CARD->name),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            "user_id" => $this->user()->id,
            "status" => OrderStatus::PROCESSING->name,
            "payment_method" => PaymentMethod::CREDIT_CARD->name,
        ]);
    }
}
