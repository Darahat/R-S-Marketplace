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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->decimal('discount_price', 10, 2)->default(0);

            $table->unsignedBigInteger('created_by'); // user who created
            $table->unsignedBigInteger('updated_by')->nullable(); // user who last updated

            $table->timestamps();

            // Foreign keys
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
