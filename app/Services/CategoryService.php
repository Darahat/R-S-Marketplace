<?php
namespace App\Services;
use Illuminate\Support\Str;
use App\Repositories\CategoryRepository;
use Illuminate\Support\Facades\Mail;
use App\Mail\BrandCreatedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Wishlist;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
class CategoryService{
        protected $siteTitle;
  public function __construct(protected HomeService $home_service)
    {        $this->siteTitle = 'R&SMarketPlace | ';

    }
    public function getCategories(){
// Get all categories with their relationships
        $categories = Category::with(['parent', 'children'])
            ->orderBy('name')
            ->paginate(20);

        // Get root categories for tree view
        $rootCategories = Category::with('children.children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

            return [
                'categories' => $categories,
                'rootCategories' =>$rootCategories,
            ];
    }
        public function getCategoriesWithLevel($excludeId = null)
    {
        $categories = Category::with('parent')->orderBy('name')->get();
        $result = [];

        foreach ($categories as $category) {
            if ($excludeId && $category->id == $excludeId) {
                continue;
            }

            $level = 0;
            $parent = $category->parent;
            while ($parent) {
                $level++;
                $parent = $parent->parent;
            }

            $category->level = $level;
            $result[] = $category;
        }

        return collect($result)->sortBy(['level', 'name']);
    }
    public function createCategory(array $data){
        if (isset($data['image'])) {
            $data['image'] = $data['image']->store('categories', 'public');
        }

        $data['slug'] = Str::slug($data['name']);
        $data['status'] = $data['status'] ?? true;
        $data['is_featured'] = $data['is_featured'] ?? false;
        $data['is_new'] = $data['is_new'] ?? false;
        $data['discount_price'] = $data['discount_price'] ?? 0;
        $data['created_by'] = Auth::id();
        return Category::create($data);
    }
    public function getCategoryDetails($id){
        return Category::with(['parent', 'children', 'products', 'creator', 'updater'])
            ->findOrFail($id);
    }

    public function updateCategory(array $data,int $id){
        Log::info($data);
                Log::info($id);
// Prevent circular reference
        $category = Category::findOrFail($id);
        $category->name = $data['name'];
        $category->slug = Str::slug($data['name']);
        $category->description = $data['description'];
        $category->parent_id = $data['parent_id'];
        $category->status = $data['status'] ?? true;
        $category->is_featured = $data['is_featured'] ?? false;
        $category->is_new = $data['is_new'] ?? false;
        $category->discount_price = $data['discount_price'] ?? 0;
       if(isset($data['image'])){
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->image = $data['image'];
    }
        $category->updated_by = Auth::id();
        $updatedCategory = $category->save();
        return [
            'updatedCategory' =>$updatedCategory,
        ];
    }
    public function toggleStatus(int $id):int{

        $category = Category::findOrFail($id);
        Log::info($category->status);
        $category->status = !$category->status;
        $category->updated_by = Auth::id();
        $category->save();
        return (int) $category->status;
    }
    public function toggleFeature(int $id):int{
        $category = Category::findOrFail($id);
        $category->is_featured = !$category->is_featured;
        $category->updated_by = Auth::id();
         $category->save();
        return (int) $category->is_featured;
    }
    public function getTree():Array{
        $categories = Category::with('children.children')
            ->whereNull('parent_id')
            ->where('status', true)
            ->orderBy('name')
            ->get();
        return $categories;
    }
        public function getCategoryPageData(string $slug, array $filters){
            $category = Category::where('slug', $slug)->firstOrFail();

        $allCategories = Category::
            where('status', true)
            ->orderBy('name', 'asc')
            ->get();

        $categories = $allCategories->whereNull('parent_id');
        $subcategories = $allCategories->whereNotNull('parent_id');

        foreach ($categories as $cat) {
            $cat->subcategories = $subcategories->where('parent_id', $cat->id)->values();
        }

        $brands = Brand::where('category_id', $category->id)
            ->where('status', true)
            ->orderBy('name', 'asc')
            ->get();
        $productsQuery = Product::where('category_id', $category->id);

    if (!empty($filters['brandIds'])) {
        $productsQuery->whereIn('brand_id', $filters['brandIds']);
    };
    if (!empty($filters['search'])) {
        $productsQuery->where('name', 'like', '%' . $filters['search'] . '%');
    }
$products = $productsQuery->paginate(10);
 $wishlistIds = $this->home_service->getWishlistProductIds();

    $products->setCollection(
        $this->home_service->markWishlisted(
            $products->getCollection(),
            $wishlistIds
        )
    );
// 6. Final return
    return [
        'data' => [
            'title' => $this->siteTitle . 'Category',
            'page' => 'category',
        ],
        'allCategories' => $allCategories,
        'categories' => $categories,
        'category' => $category,
        'category_name' => $category->name,
        'products' => $products,
        'brands' => $brands,
    ];
    }
}
