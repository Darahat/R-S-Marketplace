<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use Hash;
use Session;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    protected $sms_api;
    protected $db_controller;
    protected $page_title;

	public function __construct(){

        $this->page_title = "Admin Panel";

    }

    public function viewProduct(){

        $products = DB::table('products')->paginate(10);
        return view('backend_panel_view.pages.findProduct', compact('products')+ [
            'page_title' =>  $this->page_title,
            'page_header' => 'Product List',

        ]);
    }

    public function destroy($id)
    {

        DB::table('products')->where('id', $id)->delete();

        // if ($product->image && file_exists(public_path('uploads/products/' . $product->image))) {
        //     unlink(public_path('uploads/products/' . $product->image));
        // }
        return redirect()->back()->with('success', 'Product deleted successfully.');
    }



}
