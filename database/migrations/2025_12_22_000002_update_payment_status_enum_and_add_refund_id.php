<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Consolidated in base create: guard and skip enum raw SQL
        if (!Schema::hasColumn('payments', 'stripe_refund_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('stripe_refund_id')->nullable()->after('stripe_payment_intent_id');
            });
        }
        // Payment status enum already includes refund_pending in base migration; skipping alteration
    }

    public function down(): void
    {
        if (Schema::hasColumn('payments', 'stripe_refund_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('stripe_refund_id');
            });
        }
        // Leave enum as-is; base migration defines it
    }
};
