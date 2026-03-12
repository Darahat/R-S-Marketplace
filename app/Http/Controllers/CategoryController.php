<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    protected $page_title;

    public function __construct(protected CategoryService $category_service)
    {
        $this->page_title = "Category Management";
    }

    /**
     * Display a listing of categories with tree structure
     */
    public function index()
    {

        $getAllCategories = $this->category_service->getCategories();

        return view('backend_panel_view_admin.pages.categories.index', [
            'categories' => $getAllCategories['categories'],
            'rootCategories' => $getAllCategories['rootCategories'],
            'page_title' => $this->page_title,
            'page_header' => 'Categories',
        ]);
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $categories = $this->category_service->getCategoriesWithLevel();

        return view('backend_panel_view_admin.pages.categories.create', [
            'categories' => $categories,
            'page_title' => $this->page_title,
            'page_header' => 'Add New Category',
        ]);
    }

    /**
     * Store a newly created category
     */
    public function store(CategoryRequest $request)
    {

         $this->category_service->createCategory($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully!');
    }

    /**
     * Display the specified category details
     */
    public function show($id)
    {
        $category = $this->category_service->getCategoryDetails($id);

        return view('backend_panel_view_admin.pages.categories.partials.show',compact('category'))->render();
    }

    /**
     * Show the form for editing a category
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);

        // Get all categories with level excluding current category
        $categories = $this->category_service->getCategoriesWithLevel($id);

        return view('backend_panel_view_admin.pages.categories.edit', [
            'category' => $category,
            'categories' => $categories,
            'page_title' => $this->page_title,
            'page_header' => 'Edit Category',
        ]);
    }

    /**
     * Update the specified category
     */
    public function update(CategoryRequest $request, $id)
    {

        $data = $request->validated();
       if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }
       $categoryUpdate = $this->category_service->updateCategory($data,$id);

        // Prevent circular reference
        if (!$categoryUpdate) {
            return redirect()->back()
                ->with('error', 'Category Update Failed')
                ->withInput();
        }


        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified category
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Check if category has children
        if ($category->children()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete category with sub-categories!');
        }

        // Check if category has products
        if ($category->products()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete category with associated products!');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully!');
    }

    /**
     * Toggle category status
     */
    public function toggleStatus($id)
    {
        $categoryStatus = $this->category_service->toggleStatus($id);
        if(!$categoryStatus){
            return response()->json([
            'success' => false,
            'status' => $categoryStatus,
            'message' => 'Category status update failed!'
        ]);
        }
        return response()->json([
            'success' => true,
            'status' => $categoryStatus,
            'message' => 'Category status updated successfully!'
        ]);
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured($id)
    {
        $categoryIsFeatured = $this->category_service->toggleFeature($id);
         if(!$categoryIsFeatured){
            return response()->json([
            'success' => false,
            'status' => $categoryIsFeatured,
            'message' => 'Category Feature update failed!'
        ]);
        }
        return response()->json([
            'success' => true,
            'is_featured' => $categoryIsFeatured,
            'message' => 'Featured status updated successfully!'
        ]);
    }

    /**
     * Get category tree as JSON
     */
    public function getTree()
    {

        $categories = $this->category_service->getTree();

        return response()->json($categories);
    }


}
