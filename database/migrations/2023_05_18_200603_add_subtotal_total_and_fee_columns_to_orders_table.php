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
        Schema::table("orders", function (Blueprint $table) {
            $table
                ->decimal("subtotal", 10)
                ->nullable()
                ->after("card");
            $table
                ->decimal("total", 10)
                ->nullable()
                ->after("subtotal");
            $table
                ->decimal("fees", 10)
                ->nullable()
                ->after("total");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("orders", function (Blueprint $table) {
            $table->dropColumn(["total", "subtotal", "fee"]);
        });
    }
};
