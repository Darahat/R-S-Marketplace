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
use App\Models\Category;
use App\Models\Product;
use App\Models\Wishlist;
use App\Services\CategoryService;
use App\Services\HomeService;
use App\Services\ProductService;

class HomeController extends Controller
{
    protected $siteTitle;

    function __construct(protected HomeService $homeService,protected ProductService $product_service, protected CategoryService $category_service)
    {
        $this->siteTitle = 'R&SMarketPlace | ';
    }



    function index(Request $request)
    {
        $data = array();
        $data['title'] = $this->siteTitle . 'Home';
        $data['page'] = 'home';
        $serviceReturnData = $this->homeService->index($data);
        Log::info($serviceReturnData['allCategories']);
        return view('frontend_view.pages.homepage',[
                'latestProducts' => $serviceReturnData['latestProducts'],
                'bestSellingProducts' => $serviceReturnData['bestSellingProducts'],
                'discountProducts' => $serviceReturnData['discountProducts'],
                'regularProducts' => $serviceReturnData['regularProducts'],
                'suggestedProducts' => $serviceReturnData['suggestedProducts'],
                'hero' => $serviceReturnData['heroSettings'],
                'data' => $data,
                // 'categories' => $serviceReturnData['categories'],
                'allCategories' => $serviceReturnData['allCategories']
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

    }

    public function product($slug)
    {
        $data = array();
        $data['title'] = $this->siteTitle . 'Category';
        $data['page'] = 'category';

        $serviceData = $this->homeService->homePageProduct($slug);


        return view('frontend_view.pages.product_view', [
            'data' =>  $data,
            'product' => $serviceData['product'],
            'reviews' => $serviceData['reviews'],
            'averageRating' => round($serviceData['averageRating'], 1),
            'reviewCount' => $serviceData['reviewCount'],
        ]);
    }

    public function search(Request $request)
    {
        $data = [
            'title' => $this->siteTitle . 'Search Results',
            'page'  => 'search',
        ];
    $filters = [
        'search'    => $request->input('q', ''),
        'brands'    => $request->filled('brands') ? explode(',', $request->input('brands')) : [],
        'categories'=> $request->input('categories'),
        'min_price' => $request->input('min_price'),
        'max_price' => $request->input('max_price'),
    ];
        $query = $request->input('q', '');

        $resultData = $this->homeService->homeSearch($filters);


        if ($request->filled('min_price')) {
            $resultData['productsQuery']->where('price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $resultData['productsQuery']->where('price', '<=', $request->input('max_price'));
        }
        if (request()->ajax()) {
            return view('frontend_view.components.cards.productGrid', [
                'products' => $resultData['products'],
            ])->render();
        }

        return view('frontend_view.pages.search', [
            'data' => $data,
            'products' => $resultData['products'],
            'query' => $query,
            'categories' => $resultData['categories'],
            'allCategories' => $resultData['allCategories'],
        ]);
    }

}

