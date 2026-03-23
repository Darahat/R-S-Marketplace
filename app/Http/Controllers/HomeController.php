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
use App\Services\CategoryService;
use App\Services\HomeService;
class HomeController extends Controller
{
    protected $siteTitle;

    function __construct(protected HomeService $homeService, protected CategoryService $category_service)
    {
        $this->siteTitle = 'R&SMarketPlace | ';
    }



    function index(Request $request)
    {
        $data = array();
        $data['title'] = $this->siteTitle . 'Home';
        $data['page'] = 'home';
        $serviceReturnData = $this->homeService->index($data);
        // dd($serviceReturnData['categories'],$serviceReturnData['allCategories']);
        return view('frontend_view.pages.homepage',[
                'latestProducts' => $serviceReturnData['latestProducts'],
                'bestSellingProducts' => $serviceReturnData['bestSellingProducts'],
                'discountProducts' => $serviceReturnData['discountProducts'],
                'regularProducts' => $serviceReturnData['regularProducts'],
                'suggestedProducts' => $serviceReturnData['suggestedProducts'],
                'hero' => $serviceReturnData['heroSettings'],
                'data' => $data,
                'categories' => $serviceReturnData['categories'],
                'allCategories' => $serviceReturnData['allCategories'],
            ]);


    }

    public function category(Request $request,$slug)
    {

$filters = [
    'brandIds' => $request->filled('brands')
        ? explode(',', $request->brands)
        : [],
    'search' => $request->search,
];

       $result = $this->category_service->getCategoryPageData($slug, $filters);


         if ($request->ajax()) {
        return view('frontend_view.components.cards.productGrid', [
            'products' => $result['products'],
            'category_name' => $result['category']->name
        ])->render();
    }

    return view('frontend_view.pages.category', $result);

        // return view('frontend_view.pages.category', [
        //     'data' => $data,
        //     'allCategories' => $allCategories,
        //     'categories' => $categories,
        //     'category_name' => $category->name,
        //     'products' => $products,
        //     'brands' => $brands,
        // ]);
    }

    public function product($slug)
    {
        $data = array();
        $data['title'] = $this->siteTitle . 'Category';
        $data['page'] = 'category';

        $serviceData = $this->homeService->homePageProduct($slug);


        return view('frontend_view.pages.product_view', [
            'data' =>  $serviceData['data'],
            'product' => $serviceData['product'],
            'reviews' => $serviceData['reviews'],
            'averageRating' => round($serviceData['$averageRating'], 1),
            'reviewCount' => $serviceData['reviewCount'],
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

        $wishlistIds = $this->homeService->getWishlistProductIds();

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
        $products->setCollection($this->homeService->markWishlisted($products->getCollection(), $wishlistIds));

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

