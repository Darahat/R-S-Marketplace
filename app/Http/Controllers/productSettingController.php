<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Repositories\BrandRepository;
use Hash;
use Session;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProductSettingController extends Controller
{
    protected $sms_api;
    protected $db_controller;
    protected $page_title;

	public function __construct(protected BrandRepository $brand_repo){

        $this->page_title = "Admin Panel";

    }

    public function viewBrand(){

        $brands = $this->brand_repo->viewPaginatedBrand();
        return view('backend_panel_view_admin.pages.addBrand', compact('brands')+ [
            'page_title' =>  $this->page_title,
            'page_header' => 'Brand List',

        ]);
    }

    public function destroy($id)
    {
        $this->brand_repo->destroyBrand($id);
        return redirect()->back()->with('success', 'Product deleted successfully.');
    }



}
