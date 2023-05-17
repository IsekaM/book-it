<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("book_cart", function (Blueprint $table) {
            $table
                ->foreignId("cart_id")
                ->constrained()
                ->cascadeOnDelete();

            $table
                ->foreignId("book_id")
                ->constrained()
                ->cascadeOnDelete();

            $table->integer("quantity")->default(1);

            $table->timestamps();

            $table->primary(["cart_id", "book_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("book_cart");
    }
};
