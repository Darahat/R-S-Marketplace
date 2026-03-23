<?php
namespace App\Services;

use App\Repositories\UserAddressRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Wishlist;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
class HomeService{
      use AuthorizesRequests;
       protected $siteTitle;
    public function __construct()
    {
        $this->siteTitle = '';
    }
    public function getWishlistProductIds(): array
    {
        if (Auth::check()) {
            $wishlist = Wishlist::where('user_id', Auth::id())->first();

            return $wishlist
                ? $wishlist->items()->pluck('product_id')->map(fn ($productId) => (int) $productId)->toArray()
                : [];
        }

        return array_map('intval', session('wishlist', []));
    }

    public function markWishlisted($products, array $wishlistIds)
    {
        $wishlistLookup = array_fill_keys($wishlistIds, true);

        return $products->map(function ($product) use ($wishlistLookup) {
            $product->is_wishlisted = isset($wishlistLookup[(int) $product->id]);

            return $product;
        });
    }
    public function index(){



        $allCategories = Category::where('status', 1)
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

        $productBase = Product::where('stock', '>', 0);

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

        return [
            'categories' => $categories,
            'allCategories' => $allCategories,
            'heroSettings' => $heroSettings,
            'wishlistIds'=>$wishlistIds,
            'productBase'=>$productBase,
            'latestProducts'=>$latestProducts,
            'bestSellingProducts'=>$bestSellingProducts,
            'discountProducts'=>$discountProducts,
            'regularProducts'=>$regularProducts,
            'suggestedProducts'=>$suggestedProducts
        ];
    }

    public function homePageProduct(String $slug):array{


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
    return [];
            }

}
