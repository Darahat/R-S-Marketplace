<?php
namespace App\Services;

use App\Repositories\HomeRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class HomeService
{
    use AuthorizesRequests;

    protected $siteTitle;

    public function __construct(
        protected ProductService $product_service,
        protected HomeRepository $repo,
    ) {
        $this->siteTitle = '';
    }
    public function getWishlistProductIds(): array
    {
        if (Auth::check()) {
            $wishlist = $this->repo->getWishlistForUser(Auth::id());

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
    public function index()
    {
        $allCategories = $this->repo->getActiveCategories();

        // Group by parent_id
        $categories = $allCategories->whereNull('parent_id');
        $subcategories = $allCategories->whereNotNull('parent_id');

        // Attach subcategories to each main category
        foreach ($categories as $category) {
            $category->subcategories = $subcategories->where('parent_id', $category->id)->values();
        }

        // Hero settings from DB (fallback to defaults)
        $heroDefaults = [
            'headline' => 'Next-Gen Tech for 2025',
            'highlight' => '2025',
            'subheadline' => 'Discover the most innovative gadgets that will redefine your digital experience. Cutting-edge technology at your fingertips.',
            'show_overlay' => true,
            'overlay_color' => '#000000',
            'headline_color' => '#FFFFFF',
            'highlight_color' => '#FCD34D',
            'subheadline_color' => '#E5E7EB',
            'primary_text' => 'Shop Now',
            'primary_url' => url('/'),
            'secondary_text' => 'Explore Deals',
            'secondary_url' => url('/'),
            'banner_image' => null,
        ];

        $heroModel = $this->repo->getHeroSection();

        // One-time migration from legacy JSON if table is empty
        if (!$heroModel && Storage::disk('local')->exists('hero_section.json')) {
            $json = json_decode(Storage::disk('local')->get('hero_section.json'), true);
            if (is_array($json)) {
                $heroModel = $this->repo->createHeroSection(array_merge($heroDefaults, $json));
            }
        }

        $heroSettings = $heroModel ? array_merge($heroDefaults, $heroModel->toArray()) : $heroDefaults;
        $wishlistIds = $this->getWishlistProductIds();

        $productBase = $this->repo->getInStockProductsQuery();

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

    public function homePageProduct(string $slug): array
    {
        $product = $this->repo->getProductBySlug($slug);

        // Get product reviews
        $reviews = $product->reviews;
        $averageRating = $product->reviews_avg_rating;
        $reviewCount = $product->review_count;
    return [
        'product' => $product,
        'reviews' => $reviews,
        'averageRating' => $averageRating,
        'reviewCount' => $reviewCount
    ];
            }

    public function homeSearch($filters)
    {
        $allCategories = $this->repo->getActiveCategories();

        $categories = $allCategories->whereNull('parent_id');
        $subcategories = $allCategories->whereNotNull('parent_id');

        foreach ($categories as $category) {
            $category->subcategories = $subcategories->where('parent_id', $category->id)->values();
        }

        $wishlistIds = $this->getWishlistProductIds();
        // Search products
        $productsQuery = $this->repo->getSearchProductsQuery();
        $productsQuery = $this->product_service->index($productsQuery, $filters);
        $products = $productsQuery->orderBy('created_at', 'desc')->paginate(12);
        $products->setCollection($this->markWishlisted($products->getCollection(), $wishlistIds));

    return [
        'productQuery' => $productsQuery,
        'products' => $products,
        'wishlistIds' => $wishlistIds,
        'categories' => $categories,
        'allCategories' => $allCategories,

    ];
        }

}
