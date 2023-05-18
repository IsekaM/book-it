<?php

use App\Enums\CartStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("carts", function (Blueprint $table) {
            $table
                ->enum("status", [
                    CartStatus::OPENED->name,
                    CartStatus::CLOSED->name,
                ])
                ->default(CartStatus::OPENED->name)
                ->after("user_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("carts", function (Blueprint $table) {
            $table->dropColumn("status");
        });
    }
};
