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
        Schema::create("book_order", function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId("book_id")
                ->constrained()
                ->cascadeOnDelete();

            $table
                ->foreignId("order_id")
                ->constrained()
                ->cascadeOnDelete();

            $table->integer("quantity")->default(1);
            $table->decimal("price", 10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("book_order");
    }
};
