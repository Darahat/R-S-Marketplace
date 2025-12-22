<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Consolidated in base create: guard to avoid altering schema if columns exist
        if (Schema::hasColumn('orders', 'stripe_session_id') || Schema::hasColumn('orders', 'stripe_payment_intent_id')) {
            return; // already handled in base migration
        }
        Schema::table('orders', function (Blueprint $table) {
            $table->string('stripe_session_id')->nullable()->after('payment_method');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $dropCols = [];
        if (Schema::hasColumn('orders', 'stripe_session_id')) {
            $dropCols[] = 'stripe_session_id';
        }
        if (Schema::hasColumn('orders', 'stripe_payment_intent_id')) {
            $dropCols[] = 'stripe_payment_intent_id';
        }
        if (!empty($dropCols)) {
            Schema::table('orders', function (Blueprint $table) use ($dropCols) {
                $table->dropColumn($dropCols);
            });
        }
    }
};
