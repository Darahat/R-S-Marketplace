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
        // Consolidated in base create: order_status already includes 'to_pay'
        // Skip altering enum to avoid MySQL enum churn
        return;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Leave enum as defined in base migration
        return;
    }
};
