<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class BookRequest extends FormRequest
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
            "author" => ["string", "required"],
            "isbn" => ["string", "required", "unique:App\Models\Book,isbn"],
            "title" => ["string", "required"],
            "price" => ["required", "decimal:0,2"],
            "quantity" => ["required", "integer", "min:1"],
        ];
    }
}
