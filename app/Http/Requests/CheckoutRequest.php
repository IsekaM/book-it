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
        $requiredIfOrderIsEmpty = Rule::requiredIf(
            empty($this->route("cart")?->order),
        );

        return [
            "user_id" => ["exists:App\Models\User,id", $requiredIfOrderIsEmpty],
            "address_line_1" => ["string", $requiredIfOrderIsEmpty],
            "address_line_2" => ["string", "nullable", $requiredIfOrderIsEmpty],
            "city" => ["string", $requiredIfOrderIsEmpty],
            "parish" => ["string", $requiredIfOrderIsEmpty],
            "phone_number" => ["string", $requiredIfOrderIsEmpty],
            "status" => [
                "string",
                Rule::in([OrderStatus::PROCESSING->name]),
                $requiredIfOrderIsEmpty,
            ],
            "payment_method" => [
                "string",
                Rule::in(PaymentMethod::CREDIT_CARD->name),
                $requiredIfOrderIsEmpty,
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (empty($this->route("cart")?->order)) {
            $this->merge([
                "user_id" => $this->user()->id,
                "status" => OrderStatus::PROCESSING->name,
                "payment_method" => PaymentMethod::CREDIT_CARD->name,
            ]);
        }
    }
}
