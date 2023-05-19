<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\User;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "address_line_1" => fake()->address(),
            "city" => fake()->city(),
            "parish" => "Kingston",
            "payment_date" => fake()->dateTime(),
            "payment_method" => PaymentMethod::CREDIT_CARD->name,
            "transaction_id" => fake()->numerify("SB-##-#-#-##########"),
            "cart_id" => Cart::factory(),
            "user_id" => User::factory(),
            "status" => fake()->randomElement([
                OrderStatus::PAID->name,
                OrderStatus::PROCESSING->name,
                OrderStatus::PAYMENT_FAILED->name,
            ]),
        ];
    }
}
