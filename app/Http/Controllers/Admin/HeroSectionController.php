<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\HeroSection;
use App\Services\HeroSectionService;
use App\Http\Requests\HeroSectionRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class HeroSectionController extends Controller
{

    use AuthorizesRequests;

    protected string $pageTitle = 'Hero Section Settings';

    public function __construct( private HeroSectionService $service){}


    public function edit()
    {

        $hero = $this->service->edit();
        return view('backend_panel_view_admin.pages.hero.edit', [
            'page_title' => $this->pageTitle,
            'page_header' => 'Hero Section',
            'hero' => $hero->toArray(),
        ]);
    }

    public function update(HeroSectionRequest $request)
    {
        $this->service->update($request->validated(),
        $request->file('banner_image'));

        return redirect()->back()->with('success', 'Hero section updated successfully.');
    }
}
