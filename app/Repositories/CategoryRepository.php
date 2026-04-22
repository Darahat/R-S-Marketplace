<?php

namespace App\Repositories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository
{
    public function getPaginatedCategories(int $perPage = 20): LengthAwarePaginator
    {
        return Category::with(['parent', 'children'])
            ->withCount('products')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function getRootCategoriesTree(): Collection
    {
        return Category::with('children.children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
    }

    public function getCategoriesWithParent(): Collection
    {
        return Category::with('parent')
            ->orderBy('name')
            ->get();
    }

    public function createCategory(array $data): Category
    {
        return Category::create($data);
    }

    public function findCategoryOrFail(int $id): Category
    {
        return Category::findOrFail($id);
    }

    public function saveCategory(Category $category): bool
    {
        return $category->save();
    }

    public function findCategoryDetailsOrFail(int $id): Category
    {
        return Category::with(['parent', 'children', 'products', 'creator', 'updater'])
            ->findOrFail($id);
    }

    public function getActiveCategoryTree(): Collection
    {
        return Category::with('children.children')
            ->whereNull('parent_id')
            ->where('status', true)
            ->orderBy('name')
            ->get();
    }

    public function findCategoryBySlugOrFail(string $slug): Category
    {
        return Category::where('slug', $slug)->firstOrFail();
    }

    public function getActiveCategories(): Collection
    {
        return Category::where('status', true)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getActiveBrandsByCategoryId(int $categoryId): Collection
    {
        return Brand::where('category_id', $categoryId)
            ->where('status', true)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getProductsByCategoryAndFilters(int $categoryId, array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $productsQuery = Product::where('category_id', $categoryId)
            ->withAvg('reviews as average_rating', 'rating')
            ->withCount('reviews as review_count');

        if (!empty($filters['brandIds'])) {
            $productsQuery->whereIn('brand_id', $filters['brandIds']);
        }

        if (!empty($filters['search'])) {
            $productsQuery->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $productsQuery->paginate($perPage);
    }
}
