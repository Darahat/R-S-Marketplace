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
            // Change status column definition to order_status
            DB::statement("ALTER TABLE orders CHANGE COLUMN `status` `order_status` ENUM('pending','confirmed','processing','shipped','delivered','cancelled','returned') DEFAULT 'pending'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            DB::statement("ALTER TABLE orders CHANGE COLUMN `order_status` `status` ENUM('pending','confirmed','processing','shipped','delivered','cancelled','returned') DEFAULT 'pending'");
        });
    }
};
