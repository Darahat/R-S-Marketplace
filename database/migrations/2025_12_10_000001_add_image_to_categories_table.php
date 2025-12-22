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
        if (Schema::hasColumn('categories', 'image')) {
            return; // already handled in base migration
        }
        Schema::table('categories', function (Blueprint $table) {
            $table->string('image')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('categories', 'image')) {
            return;
        }
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
