<?php

namespace App\Repositories;

use App\Models\HeroSection;

class HeroSectionRepository
{
    public function first(): ?HeroSection
    {
        return HeroSection::first();
    }

    public function firstOrCreate(): HeroSection
    {
        return HeroSection::firstOrCreate([]);
    }

    public function create(array $data): HeroSection
    {
        return HeroSection::create($data);
    }

    public function update(HeroSection $hero, array $data): bool
    {
        return $hero->update($data);
    }
}
