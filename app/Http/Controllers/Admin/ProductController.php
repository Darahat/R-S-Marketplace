<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    protected $page_title;

    public function __construct()
    {
        $this->page_title = "Product Management";
    }

    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand']);

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('slug', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Category filter
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        // Brand filter
        if ($request->has('brand') && $request->brand != '') {
            $query->where('brand_id', $request->brand);
        }

        // Status filter
        if ($request->has('status')) {
            switch ($request->status) {
                case 'featured':
                    $query->where('featured', true);
                    break;
                case 'best_selling':
                    $query->where('is_best_selling', true);
                    break;
                case 'latest':
                    $query->where('is_latest', true);
                    break;
                case 'flash_sale':
                    $query->where('is_flash_sale', true);
                    break;
                case 'low_stock':
                    $query->where('stock', '<', 10);
                    break;
            }
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(20);
        $categories = Category::where('status', true)->orderBy('name')->get();
        $brands = Brand::where('status', true)->orderBy('name')->get();

        return view('backend_panel_view.pages.products.index', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'page_title' => $this->page_title,
            'page_header' => 'Products',
        ]);
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        $categories = Category::where('status', true)->orderBy('name')->get();
        $brands = Brand::where('status', true)->orderBy('name')->get();

        return view('backend_panel_view.pages.products.create', [
            'categories' => $categories,
            'brands' => $brands,
            'page_title' => $this->page_title,
            'page_header' => 'Add New Product',
        ]);
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'rating' => 'nullable|numeric|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->description = $request->description;
        $product->price = $request->price;
        $product->purchase_price = $request->purchase_price ?? 0;
        $product->discount_price = $request->discount_price ?? 0;
        $product->stock = $request->stock;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->featured = $request->has('featured');
        $product->is_best_selling = $request->has('is_best_selling');
        $product->is_latest = $request->has('is_latest');
        $product->is_flash_sale = $request->has('is_flash_sale');
        $product->is_todays_deal = $request->has('is_todays_deal');
        $product->rating = $request->rating ?? 4.5;

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::slug($request->name) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('products', $imageName, 'public');
            $product->image = $imagePath;
        }

        $product->save();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified product
     */
    public function show($id)
    {
        $product = Product::with(['category', 'brand'])->findOrFail($id);

        return view('backend_panel_view.pages.products.show', [
            'product' => $product,
            'page_title' => $this->page_title,
            'page_header' => 'Product Details',
        ]);
    }

    /**
     * Show the form for editing a product
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::where('status', true)->orderBy('name')->get();
        $brands = Brand::where('status', true)->orderBy('name')->get();

        return view('backend_panel_view.pages.products.edit', [
            'product' => $product,
            'categories' => $categories,
            'brands' => $brands,
            'page_title' => $this->page_title,
            'page_header' => 'Edit Product',
        ]);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:products,name,' . $id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'rating' => 'nullable|numeric|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->description = $request->description;
        $product->price = $request->price;
        $product->purchase_price = $request->purchase_price ?? 0;
        $product->discount_price = $request->discount_price ?? 0;
        $product->stock = $request->stock;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->featured = $request->has('featured');
        $product->is_best_selling = $request->has('is_best_selling');
        $product->is_latest = $request->has('is_latest');
        $product->is_flash_sale = $request->has('is_flash_sale');
        $product->is_todays_deal = $request->has('is_todays_deal');
        $product->rating = $request->rating ?? $product->rating;

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . Str::slug($request->name) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('products', $imageName, 'public');
            $product->image = $imagePath;
        }

        $product->save();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Delete image if exists
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully!'
        ]);
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured($id)
    {
        $product = Product::findOrFail($id);
        $product->featured = !$product->featured;
        $product->save();

        return response()->json([
            'success' => true,
            'featured' => $product->featured,
            'message' => 'Featured status updated successfully!'
        ]);
    }

    /**
     * Bulk delete products
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No products selected!'
            ]);
        }

        $products = Product::whereIn('id', $ids)->get();

        foreach ($products as $product) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $product->delete();
        }

        return response()->json([
            'success' => true,
            'message' => count($ids) . ' products deleted successfully!'
        ]);
    }
}
