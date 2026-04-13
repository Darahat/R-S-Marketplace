<?php
namespace App\Services;
use App\Repositories\WishlistRepository;
use Illuminate\Support\Facades\Auth;
use App\Services\CartService;

class WishlistService{
    public function __construct(
            protected CartService $cartService,
            protected WishlistRepository $repo,
    )
    {

    }

    /**
     * Sync guest wishlist to database when user logs in
     */

      public function syncGuestWishlist($id):void
    {
        if (session()->has('wishlist')) {
            $guestWishlist = session('wishlist', []);
            $wishlist = $this->repo->firstOrCreateForUser($id);

            foreach ($guestWishlist as $productId) {
                $this->repo->firstOrCreateItem($wishlist->id, (int) $productId);
            }

            session()->forget('wishlist');
        }
    }
    public function getWishlistItems():Array
    {
        if (Auth::check()) {
            $wishlist = $this->repo->firstOrCreateForUser(Auth::id());
            return $this->repo->getItemsWithProduct($wishlist)->map(function ($item) {
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
            $product = $this->repo->findProduct((int) $productId);
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
            $wishlist = $this->repo->findForUser(Auth::id());
            return $wishlist ? $this->repo->countItems($wishlist) : 0;
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
            $wishlist = $this->repo->findForUser(Auth::id());
            if ($wishlist) {
                $this->repo->deleteItem($wishlist->id, $productId);
                $wishlist->refresh(); // Refresh to get updated count
                $wishlistCount = $this->repo->countItems($wishlist);
            } else {
                $wishlistCount = 0;
            }
        } else {
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
            $wishlist = $this->repo->findForUser(Auth::id());
            if ($wishlist) {
                $this->repo->deleteItem($wishlist->id, $productId);

                $wishlistCount = $this->repo->countItems($wishlist);
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
            $wishlist = $this->repo->firstOrCreateForUser(Auth::id());
            $wishlistItem = $this->repo->findItem($wishlist->id, $productId);

            if ($wishlistItem) {
                $this->repo->deleteWishlistItem($wishlistItem);
                $is_wishlisted = false;
            } else {
                $this->repo->createItem($wishlist->id, $productId);
                $is_wishlisted = true;
            }

            $count = $this->repo->countItems($wishlist);
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
