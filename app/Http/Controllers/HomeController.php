<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use DateTime;
use App\Models\HeroSection;
use App\Models\Wishlist;
class HomeController extends Controller
{
    protected $siteTitle;

    function __construct()
    {
        $this->siteTitle = 'R&SMarketPlace | ';
    }

    private function getWishlistProductIds(): array
    {
        if (Auth::check()) {
            $wishlist = Wishlist::where('user_id', Auth::id())->first();

            return $wishlist
                ? $wishlist->items()->pluck('product_id')->map(fn ($productId) => (int) $productId)->toArray()
                : [];
        }

        return array_map('intval', session('wishlist', []));
    }

    private function markWishlisted($products, array $wishlistIds)
    {
        $wishlistLookup = array_fill_keys($wishlistIds, true);

        return $products->map(function ($product) use ($wishlistLookup) {
            $product->is_wishlisted = isset($wishlistLookup[(int) $product->id]);

            return $product;
        });
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
        // Hero settings from DB (fallback to defaults)
        $heroDefaults = [
            'headline' => 'Next-Gen Tech for 2025',
            'highlight' => '2025',
            'subheadline' => 'Discover the most innovative gadgets that will redefine your digital experience. Cutting-edge technology at your fingertips.',
            'primary_text' => 'Shop Now',
            'primary_url' => url('/'),
            'secondary_text' => 'Explore Deals',
            'secondary_url' => url('/'),
            'banner_image' => null,
        ];

        $heroModel = \App\Models\HeroSection::first();

        // One-time migration from legacy JSON if table is empty
        if (!$heroModel && Storage::disk('local')->exists('hero_section.json')) {
            $json = json_decode(Storage::disk('local')->get('hero_section.json'), true);
            if (is_array($json)) {
                $heroModel = \App\Models\HeroSection::create(array_merge($heroDefaults, $json));
            }
        }

        $heroSettings = $heroModel ? array_merge($heroDefaults, $heroModel->toArray()) : $heroDefaults;
        $wishlistIds = $this->getWishlistProductIds();

        $productBase = DB::table('products')->where('stock', '>', 0);

        $latestProducts = $this->markWishlisted((clone $productBase)
            ->where('is_latest', true)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get(), $wishlistIds);

        $bestSellingProducts = $this->markWishlisted((clone $productBase)
            ->where('is_best_selling', true)
            ->orderBy('sold_count', 'desc')
            ->take(8)
            ->get(), $wishlistIds);

        $discountProducts = $this->markWishlisted((clone $productBase)
            ->where('discount_price', '>', 0)
            ->take(8)
            ->get(), $wishlistIds);

        $regularProducts = $this->markWishlisted((clone $productBase)
            ->inRandomOrder()
            ->take(8)
            ->get(), $wishlistIds);

        $suggestedProducts = $this->markWishlisted((clone $productBase)
            ->where('featured', true)
            ->take(8)
            ->get(), $wishlistIds);

        return view('frontend_view.pages.homepage',[
                'latestProducts' => $latestProducts,
                'bestSellingProducts' => $bestSellingProducts,
                'discountProducts' => $discountProducts,
                'regularProducts' => $regularProducts,
                'suggestedProducts' => $suggestedProducts,
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
        $wishlistIds = $this->getWishlistProductIds();
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
        $products->setCollection($this->markWishlisted($products->getCollection(), $wishlistIds));

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

    public function search(Request $request)
    {
        $data = array();
        $data['title'] = $this->siteTitle . 'Search Results';
        $data['page'] = 'search';

        $query = $request->input('q', '');

        $allCategories = DB::table('categories')
            ->where('status', true)
            ->orderBy('name', 'asc')
            ->get();

        $categories = $allCategories->whereNull('parent_id');
        $subcategories = $allCategories->whereNotNull('parent_id');

        foreach ($categories as $category) {
            $category->subcategories = $subcategories->where('parent_id', $category->id)->values();
        }

        $wishlistIds = $this->getWishlistProductIds();

        // Search products
        $products_query = DB::table('products')
            ->where('stock', '>', 0);

        if (!empty($query)) {
            $products_query->where(function($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%');
            });
        }

        // Apply filters if provided
        if ($request->filled('brands')) {
            $brandIds = explode(',', $request->input('brands'));
            $products_query->whereIn('brand_id', $brandIds);
        }

        if ($request->filled('categories')) {
            $categoryIds = $request->input('categories');
            $products_query->whereIn('category_id', $categoryIds);
        }

        if ($request->filled('min_price')) {
            $products_query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $products_query->where('price', '<=', $request->input('max_price'));
        }

        $products = $products_query->orderBy('created_at', 'desc')->paginate(12);
        $products->setCollection($this->markWishlisted($products->getCollection(), $wishlistIds));

        if (request()->ajax()) {
            return view('frontend_view.components.cards.productGrid', [
                'products' => $products,
            ])->render();
        }

        return view('frontend_view.pages.search', [
            'data' => $data,
            'products' => $products,
            'query' => $query,
            'categories' => $categories,
            'allCategories' => $allCategories,
        ]);
    }

}

