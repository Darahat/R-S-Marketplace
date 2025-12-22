<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Add stripe_refund_id
        Schema::table('payments', function (Blueprint $table) {
            $table->string('stripe_refund_id')->nullable()->after('stripe_payment_intent_id');
        });

        // Update enum to include refund_pending
        // Note: uses raw SQL for MySQL; adjust if using another DB
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_status ENUM('pending','processing','completed','failed','refund_pending','refunded') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Remove stripe_refund_id
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('stripe_refund_id');
        });

        // Revert enum (remove refund_pending)
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_status ENUM('pending','processing','completed','failed','refunded') NOT NULL DEFAULT 'pending'");
    }
};