<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_sections', function (Blueprint $table) {
            $table->boolean('show_overlay')->default(true)->after('subheadline');
            $table->string('overlay_color', 7)->default('#000000')->after('show_overlay');
            $table->string('headline_color', 7)->default('#FFFFFF')->after('overlay_color');
            $table->string('highlight_color', 7)->default('#FCD34D')->after('headline_color');
            $table->string('subheadline_color', 7)->default('#E5E7EB')->after('highlight_color');
        });
    }

    public function down(): void
    {
        Schema::table('hero_sections', function (Blueprint $table) {
            $table->dropColumn([
                'show_overlay',
                'overlay_color',
                'headline_color',
                'highlight_color',
                'subheadline_color',
            ]);
        });
    }
};
