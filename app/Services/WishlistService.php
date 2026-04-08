<?php
namespace App\Services;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Support\Facades\Log;

class WishlistService{
  public function __construct(protected CartService $cartService)
    {

    }

    /**
     * Sync guest wishlist to database when user logs in
     */

      public function syncGuestWishlist($id):void
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
    public function getWishlistItems():Array
    {
        if (Auth::check()) {
            $wishlist = Wishlist::firstOrCreate(['user_id' => Auth::id()]);
            return $wishlist->items()->with('product')->get()->map(function ($item) {
                return [
                    'id' => $item->product_id,
                    'name' => $item->product->name,
                    'price' => $item->product->price,
                    'image' => $item->product->image,
                    'slug' => $item->product->slug ?? '',
                    'stock' => $item->product->stock ?? 0,
                ];
            })->toArray();
        }

        $sessionWishlist = session('wishlist', []);
        $wishlistItems = [];

        foreach ($sessionWishlist as $productId) {
            $product = Product::find($productId);
            if ($product) {
                $wishlistItems[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image' => $product->image,
                    'slug' => $product->slug ?? '',
                    'stock' => $product->stock ?? 0,
                ];
            }
        }

        return $wishlistItems;
    }
    /**
     * Get wishlist count
     */
    public function getWishlistCount():Int
    {
        if (Auth::check()) {
            $wishlist = Wishlist::where('user_id', Auth::id())->first();
            return $wishlist ? $wishlist->items->count() : 0;
        }

        return count(session('wishlist', []));
    }
        /**
     * Move wishlist item to cart
     */
    public function wishlistMoveToCart(int $productId):Array
    {

        // Remove from wishlist
        if (Auth::check()) {
            $wishlist = Wishlist::where('user_id', Auth::id())->first();
            if ($wishlist) {
                WishlistItem::where('wishlist_id', $wishlist->id)
                    ->where('product_id', $productId)
                    ->delete();
                $wishlist->refresh(); // Refresh to get updated count
                $wishlistCount = $wishlist->items->count();
            } else {
                $wishlistCount = 0;
            }
        } else {
            Log::info('I am hitted');
            $wishlistSession = session()->get('wishlist', []);
            $wishlistSession = array_diff($wishlistSession, [$productId]);
            session()->put('wishlist', $wishlistSession);
            $wishlistCount = count($wishlistSession);
        }

        // Add to cart
        $cartData = $this->cartService->addToCart((string) $productId, '1');
        $wishlistSession = session()->get('wishlist', []);
        $wishlistSession = session()->get('wishlist', []);
        $totalQuantity = $cartData['totalQuantity'];
        $cartCount  = $cartData['cartCount'];
                   return [
    'wishlistCount' =>$wishlistCount,
    'totalQuantity' => $totalQuantity,
    'quantity' => $cartCount,
    'cart' => $cartData
];
    }

    public function removeWishlistProduct(int $productId):int{
        $wishlistCount = 0;
         if (Auth::check()) {
            $wishlist = Wishlist::where('user_id', Auth::id())->first();
            if ($wishlist) {
                WishlistItem::where('wishlist_id', $wishlist->id)
                    ->where('product_id', $productId)
                    ->delete();

                $wishlistCount = $wishlist->items->count();
            }
        } else {
            $wishlist = session()->get('wishlist', []);
            $wishlist = array_diff($wishlist, [$productId]);
            session()->put('wishlist', $wishlist);
            $wishlistCount = count($wishlist);

        }
                    return $wishlistCount;
    }

    public function wishlistToggle(int $productId):array{


        if (Auth::check()) {
            // Database storage for logged-in users
            $wishlist = Wishlist::firstOrCreate(['user_id' => Auth::id()]);
            $wishlistItem = WishlistItem::where('wishlist_id', $wishlist->id)
                ->where('product_id', $productId)
                ->first();

            if ($wishlistItem) {
                $wishlistItem->delete();
                $is_wishlisted = false;
            } else {
                WishlistItem::create([
                    'wishlist_id' => $wishlist->id,
                    'product_id' => $productId
                ]);
                $is_wishlisted = true;
            }

            $count = $wishlist->items->count();
        } else {
            // Session storage for guests
            $wishlist = session()->get('wishlist', []);

            if (in_array($productId, $wishlist)) {
                $wishlist = array_diff($wishlist, [$productId]);
                $is_wishlisted = false;
            } else {
                $wishlist[] = $productId;
                $is_wishlisted = true;
            }

            session()->put('wishlist', $wishlist);
            $count = count($wishlist);
        }

        return [
            'is_wishlisted' =>$is_wishlisted,
            'count' => $count,
        ];
    }
}
