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
        // Consolidated in base create: guard to avoid altering schema if column exists
        if (Schema::hasColumn('payments', 'stripe_payment_intent_id')) {
            return; // already handled in base migration
        }
        Schema::table('payments', function (Blueprint $table) {
            // Store the Stripe PaymentIntent ID when available
            $table->string('stripe_payment_intent_id')->nullable()->after('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('payments', 'stripe_payment_intent_id')) {
            return;
        }
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('stripe_payment_intent_id');
        });
    }
};
