<?php

namespace Database\Seeders;

use App\Models\HeroSection;
use Illuminate\Database\Seeder;

class HeroSectionSeeder extends Seeder
{
    /**
     * Seed the hero section defaults.
     */
    public function run(): void
    {
        HeroSection::updateOrCreate(
            ['id' => 1],
            [
                'headline' => 'Next-Gen Tech for',
                'highlight' => '2025',
                'subheadline' => 'Discover the most innovative gadgets that will redefine your digital experience. Cutting-edge technology at your fingertips.',
                'primary_text' => 'Shop Now',
                'primary_url' => url('/'),
                'secondary_text' => 'Explore Deals',
                'secondary_url' => url('/'),
                'banner_image' => null,
            ]
        );
    }
}
