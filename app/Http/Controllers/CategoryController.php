<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    protected $page_title;

    public function __construct()
    {
        $this->page_title = "Category Management";
    }

    /**
     * Display a listing of categories with tree structure
     */
    public function index()
    {
        // Get all categories with their relationships
        $categories = Category::with(['parent', 'children'])
            ->orderBy('name')
            ->paginate(20);

        // Get root categories for tree view
        $rootCategories = Category::with('children.children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('backend_panel_view.pages.categories.index', [
            'categories' => $categories,
            'rootCategories' => $rootCategories,
            'page_title' => $this->page_title,
            'page_header' => 'Categories',
        ]);
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $categories = $this->getCategoriesWithLevel();

        return view('backend_panel_view.pages.categories.create', [
            'categories' => $categories,
            'page_title' => $this->page_title,
            'page_header' => 'Add New Category',
        ]);
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'required|boolean',
            'is_featured' => 'nullable|boolean',
            'is_new' => 'nullable|boolean',
            'discount_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->description = $request->description;
        if ($request->hasFile('image')) {
            $category->image = $request->file('image')->store('categories', 'public');
        }
        $category->parent_id = $request->parent_id;
        $category->status = $request->status ?? true;
        $category->is_featured = $request->is_featured ?? false;
        $category->is_new = $request->is_new ?? false;
        $category->discount_price = $request->discount_price ?? 0;
        $category->created_by = Auth::id();
        $category->save();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully!');
    }

    /**
     * Display the specified category details
     */
    public function show($id)
    {
        $category = Category::with(['parent', 'children', 'products', 'creator', 'updater'])
            ->findOrFail($id);

        $html = '
        <div class="row">
            <div class="col-md-6">
                <p><strong><i class="fas fa-tag"></i> Name:</strong> ' . $category->name . '</p>
                <p><strong><i class="fas fa-info-circle"></i> Status:</strong>
                    ' . ($category->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>') . '
                </p>
                <p><strong><i class="fas fa-star"></i> Featured:</strong>
                    ' . ($category->is_featured ? '<span class="badge bg-warning">Yes</span>' : '<span class="badge bg-secondary">No</span>') . '
                </p>
                <p><strong><i class="fas fa-certificate"></i> New:</strong>
                    ' . ($category->is_new ? '<span class="badge bg-info">Yes</span>' : '<span class="badge bg-secondary">No</span>') . '
                </p>
            </div>
            <div class="col-md-6">
                <p><strong><i class="fas fa-sitemap"></i> Parent:</strong>
                    ' . ($category->parent ? $category->parent->name : '<span class="text-muted">Root Category</span>') . '
                </p>
                <p><strong><i class="fas fa-box"></i> Products:</strong>
                    <span class="badge bg-info">' . $category->products->count() . '</span>
                </p>
                <p><strong><i class="fas fa-percentage"></i> Discount:</strong> ' . ($category->discount_price ?? 0) . '%</p>
                <p><strong><i class="fas fa-calendar"></i> Created:</strong> ' . $category->created_at->format('M d, Y h:i A') . '</p>
            </div>
        </div>';

        if ($category->description) {
            $html .= '<hr><p><strong><i class="fas fa-align-left"></i> Description:</strong></p><p>' . nl2br(e($category->description)) . '</p>';
        }

        if ($category->children->count() > 0) {
            $html .= '<hr><p><strong><i class="fas fa-folder-tree"></i> Subcategories (' . $category->children->count() . '):</strong></p><ul>';
            foreach ($category->children as $child) {
                $html .= '<li>' . $child->name . '</li>';
            }
            $html .= '</ul>';
        }

        if ($category->creator) {
            $html .= '<hr><p class="small text-muted mb-0"><strong>Created by:</strong> ' . $category->creator->name . '</p>';
        }
        if ($category->updater) {
            $html .= '<p class="small text-muted mb-0"><strong>Last updated by:</strong> ' . $category->updater->name . '</p>';
        }

        return $html;
    }

    /**
     * Show the form for editing a category
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);

        // Get all categories with level excluding current category
        $categories = $this->getCategoriesWithLevel($id);

        return view('backend_panel_view.pages.categories.edit', [
            'category' => $category,
            'categories' => $categories,
            'page_title' => $this->page_title,
            'page_header' => 'Edit Category',
        ]);
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'parent_id' => 'nullable|exists:categories,id',
            'status' => 'required|boolean',
            'is_featured' => 'nullable|boolean',
            'is_new' => 'nullable|boolean',
            'discount_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Prevent circular reference
        if ($request->parent_id == $id) {
            return redirect()->back()
                ->with('error', 'A category cannot be its own parent!')
                ->withInput();
        }

        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->description = $request->description;
        $category->parent_id = $request->parent_id;
        $category->status = $request->status ?? true;
        $category->is_featured = $request->is_featured ?? false;
        $category->is_new = $request->is_new ?? false;
        $category->discount_price = $request->discount_price ?? 0;
        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $category->image = $request->file('image')->store('categories', 'public');
        }
        $category->updated_by = Auth::id();
        $category->save();

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
        $category = Category::findOrFail($id);
        $category->status = !$category->status;
        $category->updated_by = Auth::id();
        $category->save();

        return response()->json([
            'success' => true,
            'status' => $category->status,
            'message' => 'Category status updated successfully!'
        ]);
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured($id)
    {
        $category = Category::findOrFail($id);
        $category->is_featured = !$category->is_featured;
        $category->updated_by = Auth::id();
        $category->save();

        return response()->json([
            'success' => true,
            'is_featured' => $category->is_featured,
            'message' => 'Featured status updated successfully!'
        ]);
    }

    /**
     * Get category tree as JSON
     */
    public function getTree()
    {
        $categories = Category::with('children.children')
            ->whereNull('parent_id')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    /**
     * Get all categories as flat list with hierarchy levels
     */
    private function getCategoriesWithLevel($excludeId = null)
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
}