<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class ProductRepository
{
    /**
     * Get all products with relationships
     */
    public function getAllProducts(): Collection
    {
        return Product::with(['category', 'brand'])->get();
    }

    /**
     * Find a product by ID
     */
    public function findProduct(int $id): ?Product
    {
        return Product::with(['category', 'brand'])->find($id);
    }

    /**
     * Create a new product
     */
    public function createProduct(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update a product
     */
    public function updateProduct(int $id, array $data): bool
    {
        $product = $this->findProduct($id);

        if (!$product) {
            return false;
        }

        return $product->update($data);
    }

    /**
     * Delete a product
     */
    public function deleteProduct(int $id): bool
    {
        $product = $this->findProduct($id);

        if (!$product) {
            return false;
        }

        return $product->delete();
    }

    /**
     * Get products by category
     */
    public function getProductsByCategory(int $categoryId): Collection
    {
        return Product::with(['category', 'brand'])
            ->where('category_id', $categoryId)
            ->get();
    }

    /**
     * Get products by brand
     */
    public function getProductsByBrand(int $brandId): Collection
    {
        return Product::with(['category', 'brand'])
            ->where('brand_id', $brandId)
            ->get();
    }

    /**
     * Get featured products
     */
    public function getFeaturedProducts(): Collection
    {
        return Product::with(['category', 'brand'])
            ->where('featured', true)
            ->get();
    }

    /**
     * Get best selling products
     */
    public function getBestSellingProducts(int $limit = 10): Collection
    {
        return Product::with(['category', 'brand'])
            ->where('is_best_selling', true)
            ->orderBy('sold_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get latest products
     */
    public function getLatestProducts(int $limit = 10): Collection
    {
        return Product::with(['category', 'brand'])
            ->where('is_latest', true)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get flash sale products
     */
    public function getFlashSaleProducts(): Collection
    {
        return Product::with(['category', 'brand'])
            ->where('is_flash_sale', true)
            ->get();
    }

    /**
     * Get today's deal products
     */
    public function getTodaysDeals(): Collection
    {
        return Product::with(['category', 'brand'])
            ->where('is_todays_deal', true)
            ->get();
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $threshold = 10): Collection
    {
        return Product::with(['category', 'brand'])
            ->where('stock', '<', $threshold)
            ->orderBy('stock', 'asc')
            ->get();
    }

    /**
     * Search products
     */
    public function searchProducts(string $term): Collection
    {
        return Product::with(['category', 'brand'])
            ->search($term)
            ->get();
    }

    /**
     * Get products with filters
     */
    public function getFilteredProducts(array $filters): Builder
    {
        $query = Product::with(['category', 'brand']);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (isset($filters['featured'])) {
            $query->where('featured', $filters['featured']);
        }

        if (isset($filters['in_stock']) && $filters['in_stock']) {
            $query->where('stock', '>', 0);
        }

        return $query;
    }

    /**
     * Get all categories
     */
    public function getAllCategories(): Collection
    {
        return Category::where('status', true)->orderBy('name')->get();
    }

    /**
     * Get all brands
     */
    public function getAllBrands(): Collection
    {
        return Brand::where('status', true)->orderBy('name')->get();
    }

    public function chunkProducts($ids,$size,$callback):bool
    {
        return Product::whereIn('id', $ids)
        ->chunk($size, function ($products) use ($callback) {
            $callback($products);
        });
    }

    public function getCategorizedProduct($product_category_id){

        return Product::with('category')->where('id', $product_category_id)->paginate(12);
    }
}
