<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "author" => fake()->name(),
            "title" => fake()->sentence(),
            "isbn" => fake()
                ->unique()
                ->isbn10(),
            "price" => fake()->randomFloat(2, max: 5000),
            "quantity" => fake()->randomNumber(2),
        ];
    }
}
