<?php
namespace App\Services;
use Illuminate\Support\Str;
use App\Repositories\BrandRepository;
use App\Models\Brand;
use Illuminate\Support\Facades\Mail;
use App\Mail\BrandCreatedNotification;
class BrandService{
  public function __construct(private BrandRepository $repo)
    {
    }
      public function createBrand(array $data): ?Brand{
        Log::info('Creating brand with data:', $data);
        /// Business Logic: Generate Slug
        $data['slug'] = Str::slug($data['name']);

        ///Business Logic: Format category_id array to comma-separated string
        if(isset($data['category_id']) && is_array($data['category_id'])){
            $data['category_id'] = implode(',', $data['category_id']);
        }

        $brand = $this->repo->createBrand($data);

        // Send email notification
        Mail::to('admin@example.com')->send(new BrandCreatedNotification($brand));

        return $brand;
    }
    public function updateBrand(array $data, $id):bool{
        $brand = $this->repo->findBrand($id);
         /// Business Logic: Generate Slug
        $data['slug'] = Str::slug($data['name']);
        ///Business Logic: Format category_id array to comma-separated string

        if (isset($data['category_id']) && is_array($data['category_id'])) {
            $data['category_id'] = implode(',', $data['category_id']);
        }else {
            $data['category_id'] = null;
        }


        return $this->repo->updateBrand($data, $id);
    }

    public function destroy(int $id):bool{
          $brand = $this->repo->findBrand($id);
         // Check if brand has products
        if (!$brand) {
            return false;
        }
        // Business logic: Prevent deletion if brand has products
        if ($brand->products()->count() > 0) {
            return false;
        }
        return $this->repo->destroyBrand($id);
    }

    /**
     * Toggle brand status
     * Business Logic: Flip boolean status
     */
    public function toggleStatus( object $brand): ?bool
    {
          if (!$brand) {
            return null;
        }

        $newStatus = !$brand->status;
       $success =  $this->repo->updateBrand(['status' => $newStatus], $brand->id);

        return $success;
    }

 public function getSelectedCategories(Brand $brand): array
    {
        if (empty($brand->category_id)) {
            return [];
        }

        if (is_array($brand->category_id)) {
            return $brand->category_id;
        }

        return explode(',', $brand->category_id);
    }

}
