<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add 'to_pay' to the order_status enum
            DB::statement("ALTER TABLE orders MODIFY order_status ENUM('to_pay','pending','confirmed','processing','shipped','delivered','cancelled','returned') DEFAULT 'to_pay'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Remove 'to_pay' from enum if rolling back
            DB::statement("ALTER TABLE orders MODIFY order_status ENUM('pending','confirmed','processing','shipped','delivered','cancelled','returned') DEFAULT 'pending'");
        });
    }
};
