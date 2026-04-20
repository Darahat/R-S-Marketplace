<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Collection;

class WishlistRepository
{
    public function firstOrCreateForUser(int $userId): Wishlist
    {
        return Wishlist::firstOrCreate(['user_id' => $userId]);
    }

    public function findForUser(int $userId): ?Wishlist
    {
        return Wishlist::where('user_id', $userId)->first();
    }

    public function getItemsWithProduct(Wishlist $wishlist): Collection
    {
        return $wishlist->items()->with('product')->get();
    }

    public function firstOrCreateItem(int $wishlistId, int $productId): WishlistItem
    {
        return WishlistItem::firstOrCreate([
            'wishlist_id' => $wishlistId,
            'product_id' => $productId,
        ]);
    }

    public function findItem(int $wishlistId, int $productId): ?WishlistItem
    {
        return WishlistItem::where('wishlist_id', $wishlistId)
            ->where('product_id', $productId)
            ->first();
    }

    public function createItem(int $wishlistId, int $productId): WishlistItem
    {
        return WishlistItem::create([
            'wishlist_id' => $wishlistId,
            'product_id' => $productId,
        ]);
    }

    public function deleteItem(int $wishlistId, int $productId): int
    {
        return WishlistItem::where('wishlist_id', $wishlistId)
            ->where('product_id', $productId)
            ->delete();
    }

    public function deleteWishlistItem(WishlistItem $wishlistItem): bool
    {
        return $wishlistItem->delete();
    }

    public function countItems(Wishlist $wishlist): int
    {
        return $wishlist->items()->count();
    }

    public function findProduct(int $productId): ?Product
    {
        return Product::find($productId);
    }
}
