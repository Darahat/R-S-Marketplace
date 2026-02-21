<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use App\Services\BrandService;
use App\Repositories\BrandRepository;
use App\Http\Requests\BrandRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class BrandController extends Controller
{
    use AuthorizesRequests;
    protected $page_title;


public function __construct(private BrandRepository $repo, private BrandService $service)
    {
              $this->page_title = "Brand Management";
    }
    /**
     * Display a listing of brands
     */
    public function index()
    {
        $this->authorize('viewAny', Brand::class);
        $brands = Brand::where('status', true)->orderBy('name')->paginate(10);
        $categories =  $this->repo->getAllCategory();

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
        $this->authorize('create', Brand::class);
        $categories = $this->repo->getAllCategory();

        return view('backend_panel_view.pages.brands.create', [
            'categories' => $categories,
            'page_title' => $this->page_title,
            'page_header' => 'Add New Brand',
        ]);
    }

    /**
     * Store a newly created brand
     */
    public function store(BrandRequest $request)
    {
        ///authorize
        $this->authorize('create', Brand::class);
        /// validate data
        $validated = $request->validated();
        $validated['logo'] = $request->file('logo');

        $brand = $this->service->createBrand($validated);
        ///Return message
        if(!$brand){
             return redirect()->back()
            ->with('error', 'Failed to create brand.')->withInput();
        }
        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand created successfully!');
    }

    /**
     * Show the form for editing a brand
     */
    public function edit($id)
    {   $brand = $this->repo->findBrand($id);
        $this->authorize('update', $brand);
         $categories =  $this->repo->getAllCategory();

        ///Business Logic: Format category_id array to comma-separated string
        $selectedCategories = $this->service->getSelectedCategories($brand);


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
    public function update(BrandRequest $request, $id)
    {
        $brand = $this->repo->findBrand($id);
        $this->authorize('update', $brand);
        /// validate data
        $validated = $request->validated();

        $brandUpdate = $this->service->updateBrand($validated, $id);
        if(!$brandUpdate){
            return redirect()->back()
            ->with('error', 'Brand update failed!')->withInput();
        }

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand updated successfully!');
    }

    /**
     * Remove the specified brand
     */
    public function destroy($id)
    {
        $brand = $this->repo->findBrand($id);
        $this->authorize('delete', $brand);

        $success = $this->service->destroy($id);
        if (!$success) {
            return redirect()->back()
                ->with('error', 'Cannot delete brand with associated products!');
        }
        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand deleted successfully!');
    }

    /**
     * Toggle brand status
     */
    public function toggleStatus($id)
    {
        $brand = $this->repo->findBrand($id);
        $this->authorize('update', $brand);

        $newStatus = $this->service->toggleStatus($brand);


        if($newStatus){
                return redirect()->back()
        ->with('success', 'Brand status updated successfully!');
        }else{
             return redirect()->back()
        ->with('Error', 'Brand status updated successfully!');
        }
    }


 }
