<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_sections', function (Blueprint $table) {
            $table->id();
            $table->string('headline');
            $table->string('highlight')->nullable();
            $table->string('subheadline', 500);
            $table->string('primary_text');
            $table->string('primary_url');
            $table->string('secondary_text')->nullable();
            $table->string('secondary_url')->nullable();
            $table->string('banner_image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_sections');
    }
};
