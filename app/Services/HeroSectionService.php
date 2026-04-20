<?php
namespace App\Services;

use App\Repositories\HeroSectionRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class HeroSectionService
{
    use AuthorizesRequests;

    public function __construct(private HeroSectionRepository $repo)
    {
    }

    public function edit()
    {
        $defaults = [
            'headline' => 'Next-Gen Tech for',
            'highlight' => '2025',
            'subheadline' => 'Discover the most innovative gadgets that will redefine your digital experience. Cutting-edge technology at your fingertips.',
            'primary_text' => 'Shop Now',
            'primary_url' => url('/'),
            'secondary_text' => 'Explore Deals',
            'secondary_url' => url('/'),
            'banner_image' => null,
        ];

        // Attempt to load from DB; if empty, try legacy JSON once
        $hero = $this->repo->first();

        if (!$hero) {
            $data = $defaults;
            $path = 'hero_section.json';
            if (Storage::disk('local')->exists($path)) {
                $json = json_decode(Storage::disk('local')->get($path), true);
                if (is_array($json)) {
                    $data = array_merge($data, $json);
                }
            }
            $hero = $this->repo->create($data);
        }

        return $hero;
    }

    public function update(array $data, $bannerImage = null)
    {
        $hero = $this->repo->firstOrCreate();

        // Handle banner image upload (public disk for frontend access)
        if ($bannerImage && $bannerImage->isValid()) {
            // delete old file if stored locally
            if ($hero->banner_image && Storage::disk('public')->exists($hero->banner_image)) {
                Storage::disk('public')->delete($hero->banner_image);
            }

            $data['banner_image'] = $bannerImage->store('hero', 'public');
        } else {
            // retain existing banner
            $data['banner_image'] = $hero->banner_image;
        }

        $this->repo->update($hero, $data);

        return $hero;
    }
}
