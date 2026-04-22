<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\HeroSection;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HomeRepository
{
    protected function productReviewAggregates(Builder $query): Builder
    {
        return $query
            ->withAvg('reviews as average_rating', 'rating')
            ->withCount('reviews as review_count');
    }

    public function getWishlistForUser(int $userId): ?Wishlist
    {
        return Wishlist::where('user_id', $userId)->first();
    }

    public function getActiveCategories(): Collection
    {
        return Category::where('status', 1)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getHeroSection(): ?HeroSection
    {
        return HeroSection::first();
    }

    public function createHeroSection(array $data): HeroSection
    {
        return HeroSection::create($data);
    }

    public function getInStockProductsQuery(): Builder
    {
        return $this->productReviewAggregates(
            Product::query()->where('stock', '>', 0)
        );
    }

    public function getProductBySlug(string $slug): Product
    {
        return $this->productReviewAggregates(
            Product::with(['reviews.user:id,name'])
        )
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function getSearchProductsQuery(): Builder
    {
        return $this->productReviewAggregates(
            Product::query()->where('stock', '>', 0)
        );
    }
}
