<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        ];

        $path = 'hero_section.json';
        $saved = [];
        if (Storage::disk('local')->exists($path)) {
            $json = json_decode(Storage::disk('local')->get($path), true);
            if (is_array($json)) {
                $saved = $json;
            }
        }

        return view('backend_panel_view.pages.hero.edit', [
            'page_title' => $this->pageTitle,
            'page_header' => 'Hero Section',
            'hero' => array_merge($defaults, $saved),
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
        ]);

        Storage::disk('local')->put('hero_section.json', json_encode($data, JSON_PRETTY_PRINT));

        return redirect()->back()->with('success', 'Hero section updated successfully.');
    }
}
