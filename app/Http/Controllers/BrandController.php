<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    protected $page_title;

    public function __construct()
    {
        $this->page_title = "Brand Management";
    }

    /**
     * Display a listing of brands
     */
    public function index()
    {
        $brands = Brand::orderBy('created_at', 'desc')->paginate(15);
        $categories = Category::where('status', true)->orderBy('name')->get();

        return view('backend_panel_view.pages.brands.index', [
            'brands' => $brands,
            'categories' => $categories,
            'page_title' => $this->page_title,
            'page_header' => 'Brand List',
        ]);
    }

    /**
     * Show the form for creating a new brand
     */
    public function create()
    {
        $categories = Category::where('status', true)->orderBy('name')->get();

        return view('backend_panel_view.pages.brands.create', [
            'categories' => $categories,
            'page_title' => $this->page_title,
            'page_header' => 'Add New Brand',
        ]);
    }

    /**
     * Store a newly created brand
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:brands',
            'category_id' => 'nullable|array',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $brand->category_id = $request->category_id ? implode(',', $request->category_id) : null;
        $brand->status = $request->status;
        $brand->save();

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand created successfully!');
    }

    /**
     * Show the form for editing a brand
     */
    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        $categories = Category::where('status', true)->orderBy('name')->get();
        $selectedCategories = $brand->category_id ? explode(',', $brand->category_id) : [];

        return view('backend_panel_view.pages.brands.edit', [
            'brand' => $brand,
            'categories' => $categories,
            'selectedCategories' => $selectedCategories,
            'page_title' => $this->page_title,
            'page_header' => 'Edit Brand',
        ]);
    }

    /**
     * Update the specified brand
     */
    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:brands,name,' . $id,
            'category_id' => 'nullable|array',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $brand->category_id = $request->category_id ? implode(',', $request->category_id) : null;
        $brand->status = $request->status;
        $brand->save();

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand updated successfully!');
    }

    /**
     * Remove the specified brand
     */
    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);

        // Check if brand has products
        if ($brand->products()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete brand with associated products!');
        }

        $brand->delete();

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand deleted successfully!');
    }

    /**
     * Toggle brand status
     */
    public function toggleStatus($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->status = !$brand->status;
        $brand->save();

        return response()->json([
            'success' => true,
            'status' => $brand->status,
            'message' => 'Brand status updated successfully!'
        ]);
    }
}
