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
use Illuminate\Support\Facades\Log;

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'rating' => 'nullable|numeric|min:0|max:5',
        ], [
            'name.required' => 'Product name is required',
            'name.unique' => 'A product with this name already exists',
            'price.required' => 'Price is required',
            'price.numeric' => 'Price must be a valid number',
            'price.min' => 'Price cannot be negative',
            'stock.required' => 'Stock quantity is required',
            'stock.integer' => 'Stock must be a whole number',
            'category_id.required' => 'Please select a category',
            'category_id.exists' => 'Selected category does not exist',
            'image.image' => 'File must be an image',
            'image.mimes' => 'Image must be jpeg, png, jpg, gif, or webp',
            'image.max' => 'Image size must not exceed 2MB',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the errors below and try again.');
        }

        try {
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
            $product->featured = $request->input('featured', 0) == 1;
            $product->is_best_selling = $request->input('is_best_selling', 0) == 1;
            $product->is_latest = $request->input('is_latest', 0) == 1;
            $product->is_flash_sale = $request->input('is_flash_sale', 0) == 1;
            $product->is_todays_deal = $request->input('is_todays_deal', 0) == 1;
            $product->rating = $request->rating ?? 4.5;

            // Handle image upload
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $image = $request->file('image');
                $imageName = time() . '_' . Str::slug($request->name) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('products', $imageName, 'public');

                if ($imagePath) {
                    $product->image = $imagePath;
                } else {
                    Log::error('Failed to store image for product: ' . $request->name);
                }
            } else {
                // Log if file was not uploaded or invalid
                if ($request->hasFile('image')) {
                    Log::error('Invalid image file uploaded for product: ' . $request->name);
                }
            }

            $product->save();

            $message = 'Product created successfully!';
            if ($request->hasFile('image') && !$product->image) {
                $message .= ' However, the image upload failed.';
            }

            return redirect()->route('admin.products.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create product: ' . $e->getMessage());
        }
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'rating' => 'nullable|numeric|min:0|max:5',
        ], [
            'name.required' => 'Product name is required',
            'name.unique' => 'A product with this name already exists',
            'price.required' => 'Price is required',
            'price.numeric' => 'Price must be a valid number',
            'price.min' => 'Price cannot be negative',
            'stock.required' => 'Stock quantity is required',
            'stock.integer' => 'Stock must be a whole number',
            'category_id.required' => 'Please select a category',
            'category_id.exists' => 'Selected category does not exist',
            'image.image' => 'File must be an image',
            'image.mimes' => 'Image must be jpeg, png, jpg, gif, or webp',
            'image.max' => 'Image size must not exceed 2MB',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the errors below and try again.');
        }

        try {
            $product->name = $request->name;
            $product->slug = Str::slug($request->name);
            $product->description = $request->description;
            $product->price = $request->price;
            $product->purchase_price = $request->purchase_price ?? 0;
            $product->discount_price = $request->discount_price ?? 0;
            $product->stock = $request->stock;
            $product->category_id = $request->category_id;
            $product->brand_id = $request->brand_id;
            $product->featured = $request->input('featured', 0) == 1;
            $product->is_best_selling = $request->input('is_best_selling', 0) == 1;
            $product->is_latest = $request->input('is_latest', 0) == 1;
            $product->is_flash_sale = $request->input('is_flash_sale', 0) == 1;
            $product->is_todays_deal = $request->input('is_todays_deal', 0) == 1;
            $product->rating = $request->rating ?? $product->rating;

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if it's a file path (not a URL)
                if ($product->image && !filter_var($product->image, FILTER_VALIDATE_URL)) {
                    if (Storage::disk('public')->exists($product->image)) {
                        Storage::disk('public')->delete($product->image);
                    }
                }

                $image = $request->file('image');
                $imageName = time() . '_' . Str::slug($request->name) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('products', $imageName, 'public');
                $product->image = $imagePath;
            }

            $product->save();

            return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update product: ' . $e->getMessage());
        }
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
