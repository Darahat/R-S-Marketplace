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
        // Base orders create already includes desired enums; skip
        return;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op; keep base schema
        return;
    }
};
