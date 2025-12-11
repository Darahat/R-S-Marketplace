<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\HeroSection;

class HeroSectionController extends Controller
{
    protected string $pageTitle = 'Hero Section Settings';

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
        $hero = HeroSection::first();

        if (!$hero) {
            $data = $defaults;
            $path = 'hero_section.json';
            if (Storage::disk('local')->exists($path)) {
                $json = json_decode(Storage::disk('local')->get($path), true);
                if (is_array($json)) {
                    $data = array_merge($data, $json);
                }
            }
            $hero = HeroSection::create($data);
        }

        return view('backend_panel_view.pages.hero.edit', [
            'page_title' => $this->pageTitle,
            'page_header' => 'Hero Section',
            'hero' => $hero->toArray(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'headline' => 'required|string|max:255',
            'highlight' => 'nullable|string|max:50',
            'subheadline' => 'required|string|max:500',
            'primary_text' => 'required|string|max:100',
            'primary_url' => 'required|string|max:255',
            'secondary_text' => 'nullable|string|max:100',
            'secondary_url' => 'nullable|string|max:255',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        // Get or create hero record
        $hero = HeroSection::first() ?? HeroSection::create([
            'headline' => $data['headline'],
            'highlight' => $data['highlight'] ?? null,
            'subheadline' => $data['subheadline'],
            'primary_text' => $data['primary_text'],
            'primary_url' => $data['primary_url'],
            'secondary_text' => $data['secondary_text'] ?? null,
            'secondary_url' => $data['secondary_url'] ?? null,
        ]);

        // Handle banner image upload (public disk for frontend access)
        if ($request->hasFile('banner_image') && $request->file('banner_image')->isValid()) {
            $image = $request->file('banner_image');
            // delete old file if stored locally
            if ($hero->banner_image && Storage::disk('public')->exists($hero->banner_image)) {
                Storage::disk('public')->delete($hero->banner_image);
            }
            $imagePath = $image->store('hero', 'public');
            $data['banner_image'] = $imagePath;
        } else {
            // retain existing banner
            $data['banner_image'] = $hero->banner_image;
        }

        $hero->update($data);

        return redirect()->back()->with('success', 'Hero section updated successfully.');
    }
}
