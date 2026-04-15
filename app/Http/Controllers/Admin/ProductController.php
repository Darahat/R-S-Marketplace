<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\BulkDeleteProductsRequest;
use App\Http\Requests\ProductRequest;
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
        $filters = $request->only([
            'search',
            'category',
            'brand',
            'status'
        ]);
        $products = $this->service->getFilteredPaginatedProducts($filters, 20);
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
        $product = $this->repo->findProduct($id);

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


        $this->service->updateProduct($validated, $id);

        return redirect()
        ->route('admin.products.index')
        ->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product
     */
    public function destroy($id)
    {
        $this->service->deleteProduct($id);
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
        $this->service->toggleFeatured($id);
        return response()->json([
            'success' => true,
            'message' => 'Featured status updated successfully!'
        ]);
    }

    /**
     * Bulk delete products
     */
    public function bulkDelete(BulkDeleteProductsRequest $request)
    {
        $validated = $request->validated();
        $ids = $validated['ids'];

        $success = $this->service->bulkDelete($ids);

        if( $success ){
return response()->json([
            'success' => true,
            'message' => count($ids) . ' products deleted successfully!'
        ]);
        }else{
            return response()->json([
            'error' => true,
            'message' => count($ids) . ' products deleted failed!'
        ]);
        }

    }
}
