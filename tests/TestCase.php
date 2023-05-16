<?php

namespace Tests;

use App\Models\User;
use Illuminate\Testing\TestResponse;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function loginUser(User $user): TestResponse
    {
        return $this->postJson(route("api.auth.login"), [
            "email" => $user->email,
            "password" => "password",
        ]);
    }

    protected function logoutUser(?string $token = null): TestResponse
    {
        return $this->postJson(
            route("api.auth.logout"),
            headers: ["Authorization" => "Bearer " . $token],
        );
    }
}
