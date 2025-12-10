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
            // Update payment_method enum to include 'cash', 'bkash', 'card'
            DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cash','bkash','card','cod','nagad','rocket') DEFAULT 'cash'");

            // Update payment_status enum to include 'pending'
            DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('pending','unpaid','paid','failed') DEFAULT 'pending'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Revert to original enums
            DB::statement("ALTER TABLE orders MODIFY payment_method ENUM('cod','card','bkash','nagad','rocket') DEFAULT 'cod'");
            DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('unpaid','paid','failed') DEFAULT 'unpaid'");
        });
    }
};
