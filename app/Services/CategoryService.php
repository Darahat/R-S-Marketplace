<?php
namespace App\Services;

use Illuminate\Support\Str;
use App\Repositories\CategoryRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;

class CategoryService{
        protected $siteTitle;

    public function __construct(
            protected HomeService $home_service,
            protected CategoryRepository $repo,
            protected AvifImageService $imageService
    )
    {        $this->siteTitle = 'R&SMarketPlace | ';

    }
    public function getCategories(){
        $categories = $this->repo->getPaginatedCategories(20);
        $rootCategories = $this->repo->getRootCategoriesTree();

            return [
                'categories' => $categories,
                'rootCategories' =>$rootCategories,
            ];
    }
        public function getCategoriesWithLevel($excludeId = null)
    {
        $categories = $this->repo->getCategoriesWithParent();
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
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $this->imageService->storePublicImage($data['image'], 'categories', $data['name'] ?? null);
        }

        $data['slug'] = Str::slug($data['name']);
        $data['status'] = $data['status'] ?? true;
        $data['is_featured'] = $data['is_featured'] ?? false;
        $data['is_new'] = $data['is_new'] ?? false;
        $data['discount_price'] = $data['discount_price'] ?? 0;
        $data['created_by'] = Auth::id();
        return $this->repo->createCategory($data);
    }
    public function getCategoryDetails($id){
        return $this->repo->findCategoryDetailsOrFail((int) $id);
    }

    public function updateCategory(array $data,int $id){
// Prevent circular reference
        $category = $this->repo->findCategoryOrFail($id);
        $category->name = $data['name'];
        $category->slug = Str::slug($data['name']);
        $category->description = $data['description'];
        $category->parent_id = $data['parent_id'];
        $category->status = $data['status'] ?? true;
        $category->is_featured = $data['is_featured'] ?? false;
        $category->is_new = $data['is_new'] ?? false;
        $category->discount_price = $data['discount_price'] ?? 0;
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $category->image = $this->imageService->storePublicImage($data['image'], 'categories', $data['name'] ?? $category->name, $category->image);
        }
        $category->updated_by = Auth::id();
        $updatedCategory = $this->repo->saveCategory($category);
        return [
            'updatedCategory' =>$updatedCategory,
        ];
    }
    public function toggleStatus(int $id):int{

        $category = $this->repo->findCategoryOrFail($id);
        $category->status = !$category->status;
        $category->updated_by = Auth::id();
        $this->repo->saveCategory($category);
        return (int) $category->status;
    }
    public function toggleFeature(int $id):int{
        $category = $this->repo->findCategoryOrFail($id);
        $category->is_featured = !$category->is_featured;
        $category->updated_by = Auth::id();
         $this->repo->saveCategory($category);
        return (int) $category->is_featured;
    }
    public function getTree():Array{
        $categories = $this->repo->getActiveCategoryTree();
        return $categories->all();
    }
        public function getCategoryPageData(string $slug, array $filters){
            $category = $this->repo->findCategoryBySlugOrFail($slug);

        $allCategories = $this->repo->getActiveCategories();

        $categories = $allCategories->whereNull('parent_id');
        $subcategories = $allCategories->whereNotNull('parent_id');

        foreach ($categories as $cat) {
            $cat->subcategories = $subcategories->where('parent_id', $cat->id)->values();
        }

        $brands = $this->repo->getActiveBrandsByCategoryId($category->id);
        $products = $this->repo->getProductsByCategoryAndFilters($category->id, $filters, 10);
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
