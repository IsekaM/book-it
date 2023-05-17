<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CartController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(["prefix" => "v1", "as" => "api."], function () {
    Route::group(["prefix" => "auth"], function () {
        Route::post("register", [AuthController::class, "register"])->name(
            "auth.register",
        );

        Route::post("login", [AuthController::class, "login"])->name(
            "auth.login",
        );

        Route::post("logout", [AuthController::class, "logout"])
            ->name("auth.logout")
            ->middleware("auth:sanctum");
    });

    Route::apiResource("books", BookController::class);

    Route::group(
        ["prefix" => "carts", "middleware" => "auth:sanctum"],
        function () {
            Route::post("add/{book}", [CartController::class, "add"])->name(
                "cart.add",
            );
            Route::post("remove/{book}", [
                CartController::class,
                "remove",
            ])->name("cart.remove");
        },
    );
});
