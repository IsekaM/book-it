<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("orders", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("user_id")
                ->constrained()
                ->cascadeOnDelete();
            $table
                ->foreignId("cart_id")
                ->constrained()
                ->cascadeOnDelete();
            $table->string("address_line_1");
            $table->string("address_line_2")->nullable();
            $table->string("city");
            $table->string("parish");
            $table->string("phone_number");
            $table
                ->string("transaction_id")
                ->nullable()
                ->unique();
            $table
                ->enum("payment_method", [PaymentMethod::CREDIT_CARD->name])
                ->nullable();
            $table->string("card")->nullable();
            $table->dateTime("payment_date")->nullable();
            $table
                ->enum("status", [
                    OrderStatus::PAID->name,
                    OrderStatus::PAYMENT_FAILED->name,
                    OrderStatus::PROCESSING->name,
                ])
                ->default(OrderStatus::PROCESSING->name);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("orders");
    }
};
