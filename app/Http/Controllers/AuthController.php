<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $hashedPassword = bcrypt($request->get("password"));

        $user = User::create(
            $request->except("password") + [
                "password" => $hashedPassword,
            ],
        );

        return response()->formattedJson([
            "token" => $user->createToken("app")->plainTextToken,
        ]);
    }

    public function login(LoginRequest $request)
    {
        $user = User::firstWhere("email", $request->get("email"));

        return response()->formattedJson([
            "token" => $user->createToken("app")->plainTextToken,
        ]);
    }

    public function logout(Request $request)
    {
        $request
            ->user()
            ->currentAccessToken()
            ->delete();

        return response()->formattedJson(null, Response::HTTP_NO_CONTENT);
    }
}
