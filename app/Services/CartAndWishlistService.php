<?php
namespace App\Services;
use App\Repositories\AuthRepository;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Wishlist;
use App\Models\WishlistItem;

class CartAndWishlistService{
  public function __construct(private AuthRepository $repo)
    {

    }
  public function syncGuestCart($id)
    {
        if (session()->has('cart')) {
            $guestCart = session('cart', []);
            $cart = Cart::firstOrCreate(['user_id' => $id]);

            foreach ($guestCart as $productId => $item) {
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('product_id', $productId)
                    ->first();

                if ($cartItem) {
                    $cartItem->quantity += $item['quantity'];
                    $cartItem->save();
                } else {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $productId,
                        'quantity' => $item['quantity'],
                        'price' => $item['price']
                    ]);
                }
            }

            session()->forget('cart');
        }
    }

    /**
     * Sync guest wishlist to database when user logs in or registers
     */
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
