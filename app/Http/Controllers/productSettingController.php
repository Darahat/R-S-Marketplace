<?php

namespace App\Http\Controllers;
use App\Services\BrandService;

class ProductSettingController extends Controller
{
    protected $sms_api;
    protected $db_controller;
    protected $page_title;

	public function __construct(protected BrandService $brand_service){

        $this->page_title = "Admin Panel";

    }

    public function viewBrand(){

        $brands = $this->brand_service->viewPaginatedBrand();
        return view('backend_panel_view_admin.pages.addBrand', compact('brands')+ [
            'page_title' =>  $this->page_title,
            'page_header' => 'Brand List',

        ]);
    }

    public function destroy($id)
    {
        $this->brand_service->destroy($id);
        return redirect()->back()->with('success', 'Product deleted successfully.');
    }



}
