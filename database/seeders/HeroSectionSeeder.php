<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ColumnSafeSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HeroSectionSeeder extends Seeder
{
    use ColumnSafeSeeder;

    /**
     * Seed the hero section defaults.
     */
    public function run(): void
    {
        $row = $this->filterRowByTable('hero_sections', [
            'id' => 1,
            'headline' => 'Next-Gen Tech for',
            'highlight' => '2025',
            'subheadline' => 'Discover the most innovative gadgets that will redefine your digital experience. Cutting-edge technology at your fingertips.',
            'primary_text' => 'Shop Now',
            'primary_url' => url('/'),
            'secondary_text' => 'Explore Deals',
            'secondary_url' => url('/'),
            'banner_image' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('hero_sections')->updateOrInsert(['id' => 1], $row);
    }
}
