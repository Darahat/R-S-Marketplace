<?php
namespace App\Services;
use App\Models\Wishlist;
use App\Models\WishlistItem;

class WishlistService{
  public function __construct()
    {

    }

      public function syncGuestWishlist($id)
    {
        if (session()->has('wishlist')) {
            $guestWishlist = session('wishlist', []);
            $wishlist = Wishlist::firstOrCreate(['user_id' => $id]);

            foreach ($guestWishlist as $productId) {
                WishlistItem::firstOrCreate([
                    'wishlist_id' => $wishlist->id,
                    'product_id' => $productId
                ]);
            }

            session()->forget('wishlist');
        }
    }
}
