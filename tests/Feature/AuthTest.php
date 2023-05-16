<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private User|Model $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->createOne();
    }

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

    public function testUserCanLogin()
    {
        $this->loginUser($this->user)
            ->assertOk()
            ->assertJsonStructure(["success", "data" => ["token"]]);
    }

    public function testUserCannotLoginWithInvalidCredentials()
    {
        $this->postJson(route("api.auth.login"), [
            "email" => $this->user->email,
            "password" => "wrongpassword",
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonFragment([
                "errors" => [
                    "email" => ["These credentials do not match our records."],
                ],
            ]);
    }

    public function testUserCanLogout()
    {
        $response = $this->loginUser($this->user);
        $token = $response->json("data.token");

        $this->assertNotEmpty($this->user->fresh()->tokens->toArray());

        $this->logoutUser($token)->assertNoContent();

        $this->assertEmpty($this->user->fresh()->tokens->toArray());
    }
}
