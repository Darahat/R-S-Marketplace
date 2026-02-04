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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('discount_price', 10, 2)->default(0);
            $table->integer('stock')->default(100);
            $table->integer('sold_count')->default(0);
            $table->decimal('rating', 2, 1)->default(4.5);
            $table->boolean('featured')->default(false);
            $table->boolean('is_best_selling')->default(false);
            $table->boolean('is_latest')->default(false);
            $table->boolean('is_flash_sale')->default(false);
            $table->boolean('is_todays_deal')->default(false);
            $table->string('image')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
