<?php
namespace App\Services;
use Illuminate\Support\Str;
use App\Repositories\CategoryRepository;
use App\Models\Category;
use Illuminate\Support\Facades\Mail;
use App\Mail\BrandCreatedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
class CategoryService{
  public function __construct(private CategoryRepository $repo)
    {
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
}
