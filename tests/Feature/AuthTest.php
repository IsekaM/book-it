<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanRegister()
    {
        $userData = [
            "first_name" => fake()->firstName(),
            "last_name" => fake()->lastName(),
            "password" => "P@asswrd#123",
            "email" => fake()->email(),
        ];

        $this->postJson(
            route("api.auth.register"),
            $userData,
        )->assertJsonStructure(["success", "data" => ["token"]]);
    }
}
