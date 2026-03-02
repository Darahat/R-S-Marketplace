<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\ProductService;
use App\Repositories\ProductRepository;
use App\Repositories\BrandRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class ProductController extends Controller
{
    use AuthorizesRequests;
    protected $page_title;

    public function __construct(private ProductRepository $repo,private BrandRepository $brandRepo, private ProductService $service)
    {
        $this->page_title = "Product Management";

    }

    /**
     * Display a listing of products
     */

    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand']);
        $query = $this->service->index($query, $request);

        $products = $query->orderBy('created_at', 'desc')->paginate(20);
        $categories = $this->brandRepo->getAllCategory();
        $brands =  $this->brandRepo->getAllBrands();

        return view('backend_panel_view_admin.pages.products.index', [
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
        $categories = $this->brandRepo->getAllCategory();
        $brands =  $this->brandRepo->getAllBrands();

        return view('backend_panel_view_admin.pages.products.create', [
            'categories' => $categories,
            'brands' => $brands,
            'page_title' => $this->page_title,
            'page_header' => 'Add New Product',
        ]);
    }

    /**
     * Store a newly created product
     */
    public function store(ProductRequest $request)
    {
        $validated = $request->validated();
        try{
        $this->service->createProduct($validated);


            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully!');
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

        return view('backend_panel_view_admin.pages.products.show', [
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
        $product = $this->repo->findProduct($id);
        abort_if(!$product, 404, 'Product not found');
        $categories = $this->brandRepo->getAllCategory();
        $brands =  $this->brandRepo->getAllBrands();

        return view('backend_panel_view_admin.pages.products.edit', [
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
    public function update(ProductRequest $request, $id)
    {
         $validated = $request->validated();


           $this->service->updateProduct($validated, $id,$request->file('image'));

         return redirect()
        ->route('admin.products.index')
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
