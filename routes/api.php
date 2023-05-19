<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;

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
            Route::get("/", [CartController::class, "show"])->name("cart.show");

            Route::post("checkout/{cart}", [
                CartController::class,
                "checkout",
            ])->name("cart.checkout");

            Route::get("complete-payment", [
                CartController::class,
                "completePayment",
            ])
                ->withoutMiddleware("auth:sanctum")
                ->name("cart.complete-payment");

            Route::post("add/{book}", [CartController::class, "add"])->name(
                "cart.add",
            );

            Route::post("remove/{book}", [
                CartController::class,
                "remove",
            ])->name("cart.remove");
        },
    );

    Route::get("/orders/user/{user}", [
        OrderController::class,
        "indexUser",
    ])->middleware("auth:sanctum");

    Route::apiResource("/orders", OrderController::class)
        ->except(["update", "store"])
        ->middleware("auth:sanctum");
});
