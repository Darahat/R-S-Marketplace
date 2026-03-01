<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ProductService
{
    public function __construct(private ProductRepository $repo)
    {
    }

    public function index(Builder $query,Request $request): ?Builder{
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
        return  $query;
    }
    /**
     * Create a new product
     */
    public function createProduct(array $data): ?Product
    {
        Log::info('Creating product with data:', $data);

        // Generate slug if not provided
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Set default rating if not provided
        if (!isset($data['rating']) || empty($data['rating'])) {
            $data['rating'] = 4.5;
        }

        // Set default purchase price
        if (!isset($data['purchase_price'])) {
            $data['purchase_price'] = 0;
        }

        // Set default discount price
        if (!isset($data['discount_price'])) {
            $data['discount_price'] = 0;
        }

        // Handle image upload
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $this->uploadImage($data['image'], $data['name']);
        }

        // Ensure boolean fields
        $booleanFields = ['featured', 'is_best_selling', 'is_latest', 'is_flash_sale', 'is_todays_deal'];
        foreach ($booleanFields as $field) {
            if (!isset($data[$field])) {
                $data[$field] = false;
            }
        }

        $product = $this->repo->createProduct($data);

        Log::info('Product created successfully', ['id' => $product->id]);

        return $product;
    }

    /**
     * Update an existing product
     */
    public function updateProduct(array $data, int $id): bool
    {
        Log::info('Updating product', ['id' => $id, 'data' => $data]);

        $product = $this->repo->findProduct($id);

        if (!$product) {
            Log::error('Product not found', ['id' => $id]);
            return false;
        }

        // Generate slug if name changed
        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Handle image upload
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            // Delete old image if exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $data['image'] = $this->uploadImage($data['image'], $data['name'] ?? $product->name);
        }

        // Ensure boolean fields
        $booleanFields = ['featured', 'is_best_selling', 'is_latest', 'is_flash_sale', 'is_todays_deal'];
        foreach ($booleanFields as $field) {
            if (!isset($data[$field])) {
                $data[$field] = false;
            }
        }

        $result = $this->repo->updateProduct($id, $data);

        if ($result) {
            Log::info('Product updated successfully', ['id' => $id]);
        } else {
            Log::error('Failed to update product', ['id' => $id]);
        }

        return $result;
    }

    /**
     * Delete a product
     */
    public function deleteProduct(int $id): bool
    {
        Log::info('Deleting product', ['id' => $id]);

        $product = $this->repo->findProduct($id);

        if (!$product) {
            Log::error('Product not found', ['id' => $id]);
            return false;
        }

        // Delete product image
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
            Log::info('Product image deleted', ['path' => $product->image]);
        }

        $result = $this->repo->deleteProduct($id);

        if ($result) {
            Log::info('Product deleted successfully', ['id' => $id]);
        } else {
            Log::error('Failed to delete product', ['id' => $id]);
        }

        return $result;
    }

    /**
     * Toggle product feature status
     */
    public function toggleFeatured(int $id): bool
    {
        $product = $this->repo->findProduct($id);

        if (!$product) {
            return false;
        }

        return $this->repo->updateProduct($id, [
            'featured' => !$product->featured
        ]);
    }

    /**
     * Update product stock
     */
    public function updateStock(int $id, int $quantity): bool
    {
        $product = $this->repo->findProduct($id);

        if (!$product) {
            return false;
        }

        $newStock = $product->stock + $quantity;

        if ($newStock < 0) {
            Log::warning('Attempted to set negative stock', ['id' => $id, 'quantity' => $quantity]);
            return false;
        }

        return $this->repo->updateProduct($id, [
            'stock' => $newStock
        ]);
    }

    /**
     * Upload product image
     */
    private function uploadImage(UploadedFile $image, string $productName): ?string
    {
        try {
            $imageName = time() . '_' . Str::slug($productName) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('products', $imageName, 'public');

            if ($imagePath) {
                Log::info('Product image uploaded successfully', ['path' => $imagePath]);
                return $imagePath;
            } else {
                Log::error('Failed to store product image', ['name' => $productName]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Exception while uploading product image', [
                'name' => $productName,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $threshold = 10)
    {
        return $this->repo->getLowStockProducts($threshold);
    }

    /**
     * Get featured products
     */
    public function getFeaturedProducts()
    {
        return $this->repo->getFeaturedProducts();
    }

    /**
     * Increment sold count
     */
    public function incrementSoldCount(int $id, int $quantity = 1): bool
    {
        $product = $this->repo->findProduct($id);

        if (!$product) {
            return false;
        }

        return $this->repo->updateProduct($id, [
            'sold_count' => $product->sold_count + $quantity
        ]);
    }
}
