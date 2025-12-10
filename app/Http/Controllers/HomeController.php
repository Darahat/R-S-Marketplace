<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use DateTime;
use Auth;
class HomeController extends Controller
{
    protected $siteTitle;

    function __construct()
    {
        $this->siteTitle = 'R&SMarketPlace | ';
    }

    function index(Request $request)
    {
        $data = array();
        $data['title'] = $this->siteTitle . 'Home';
        $data['page'] = 'home';


        $allCategories = DB::table('categories')
            ->where('status', true)
            ->orderBy('name', 'asc')
            ->get();

        // Group by parent_id
        $categories = $allCategories->whereNull('parent_id');
        $subcategories = $allCategories->whereNotNull('parent_id');

        // Attach subcategories to each main category
        foreach ($categories as $category) {
            $category->subcategories = $subcategories->where('parent_id', $category->id)->values();
        }
        // $data['products'] = DB::table('products')->where('status', 1)->orderBy('id', 'desc')->paginate(10);
        // Hero settings (from storage JSON fallback to defaults)
        $heroSettings = [
            'headline' => 'Next-Gen Tech for 2025',
            'highlight' => '2025',
            'subheadline' => 'Discover the most innovative gadgets that will redefine your digital experience. Cutting-edge technology at your fingertips.',
            'primary_text' => 'Shop Now',
            'primary_url' => url('/'),
            'secondary_text' => 'Explore Deals',
            'secondary_url' => url('/'),
        ];

        $heroFile = 'hero_section.json';
        if (Storage::disk('local')->exists($heroFile)) {
            $json = json_decode(Storage::disk('local')->get($heroFile), true);
            if (is_array($json)) {
                $heroSettings = array_merge($heroSettings, $json);
            }
        }

        $productBase = DB::table('products')->where('stock', '>', 0);

        return view('frontend_view.pages.homepage',[

                'latestProducts' => (clone $productBase)
                    ->where('is_latest', true)
                    ->orderBy('created_at', 'desc')
                    ->take(8)
                    ->get(),

                'bestSellingProducts' => (clone $productBase)
                    ->where('is_best_selling', true)
                    ->orderBy('sold_count', 'desc')
                    ->take(8)
                    ->get(),

                'discountProducts' => (clone $productBase)
                    ->where('discount_price', '>', 0)
                    ->take(8)
                    ->get(),

                'regularProducts' => (clone $productBase)
                    ->inRandomOrder()
                    ->take(8)
                    ->get(),

                'suggestedProducts' => (clone $productBase)
                    ->where('featured', true)
                    ->take(8)
                    ->get(),
                'hero' => $heroSettings,
                'data' => $data,
                'categories' => $categories,
                'allCategories' => $allCategories,
            ]);


    }

    public function category(Request $request,$slug)
    {
        $data = array();
        $data['title'] = $this->siteTitle . 'Category';
        $data['page'] = 'category';



        $allCategories = DB::table('categories')
            ->where('status', true)
            ->orderBy('name', 'asc')
            ->get();

        $categories = $allCategories->whereNull('parent_id');
        $subcategories = $allCategories->whereNotNull('parent_id');

        foreach ($categories as $cat) {
            $cat->subcategories = $subcategories->where('parent_id', $cat->id)->values();
        }

        $category = DB::table('categories')->where('slug', $slug)->first();
        if (!$category) {
            abort(404);
        }


        $brands = DB::table('brands')
            ->whereRaw('FIND_IN_SET(?, category_id)', [$category->id])
            ->where('status', true)
            ->orderBy('name', 'asc')
            ->get();


        $brandIds = [];
        if ($request->filled('brands')) {
            $brandIds = explode(',', $request->input('brands'));
        }

        $products_db = DB::table('products')
            ->whereRaw('FIND_IN_SET(?, category_id)', [$category->id]);
          if (!empty($brandIds)) {
                $products_db->whereIn('brand_id', $brandIds);
            }
            if (!empty($request->search)) {
                $products_db->where('name', 'like', '%' . $request->search . '%');
            }

        $products = $products_db->paginate(10);

        if (request()->ajax()) {
            return view('frontend_view.components.cards.productGrid', [
                'products' => $products,
                'category_name' => $category->name
            ])->render();
        }

        return view('frontend_view.pages.category', [
            'data' => $data,
            'allCategories' => $allCategories,
            'categories' => $categories,
            'category_name' => $category->name,
            'products' => $products,
            'brands' => $brands,
        ]);
    }

    public function product($slug)
    {
        $data = array();
        $data['title'] = $this->siteTitle . 'Category';
        $data['page'] = 'category';




        $product = DB::table('products')
            ->where('slug', $slug)->first();


        // Get product reviews
        $reviews = DB::table('reviews')
            ->join('users', 'reviews.user_id', '=', 'users.id')
            ->select('reviews.*', 'users.name as user_name')
            ->where('reviews.product_id', $product->id)
            ->orderBy('reviews.created_at', 'desc')
            ->get();

        // Calculate rating summary
        $averageRating = DB::table('reviews')
            ->where('product_id', $product->id)
            ->avg('rating');

        $reviewCount = DB::table('reviews')
            ->where('product_id', $product->id)
            ->count();

        return view('frontend_view.pages.product_view', [
            'data' => $data,
            'product' => $product,
            'reviews' => $reviews,
            'averageRating' => round($averageRating, 1),
            'reviewCount' => $reviewCount,
        ]);
    }

}
