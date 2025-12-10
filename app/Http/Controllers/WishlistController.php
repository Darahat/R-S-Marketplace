<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Models\Product;

class WishlistController extends Controller
{
    protected $siteTitle;

    function __construct()
    {
        $this->siteTitle = 'R&SMarketPlace | ';
    }

    /**
     * Get wishlist items based on authentication status
     */
    protected function getWishlistItems()
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
    public function getCount()
    {
        if (Auth::check()) {
            $wishlist = Wishlist::where('user_id', Auth::id())->first();
            return $wishlist ? $wishlist->items->count() : 0;
        }

        return count(session('wishlist', []));
    }

    /**
     * Sync guest wishlist to database when user logs in
     */
    public function syncGuestWishlist()
    {
        if (Auth::check() && session()->has('wishlist')) {
            $guestWishlist = session('wishlist', []);
            $wishlist = Wishlist::firstOrCreate(['user_id' => Auth::id()]);

            foreach ($guestWishlist as $productId) {
                WishlistItem::firstOrCreate([
                    'wishlist_id' => $wishlist->id,
                    'product_id' => $productId
                ]);
            }

            session()->forget('wishlist');
        }
    }

    /**
     * Display wishlist page
     */
    public function view()
    {
        $wishlistItems = $this->getWishlistItems();
        $data = [
            'title' => $this->siteTitle . 'Wishlist',
            'page' => 'wishlist'
        ];

        return view('frontend_view.pages.wishlist.view', [
            'wishlistItems' => $wishlistItems,
            'data' => $data,
        ]);
    }

    /**
     * Toggle product in wishlist
     */
    public function toggle(Request $request)
    {
        $productId = $request->product_id;

        if (!Product::find($productId)) {
            return response()->json(['error' => 'Product not found.'], 404);
        }

        if (Auth::check()) {
            // Database storage for logged-in users
            $wishlist = Wishlist::firstOrCreate(['user_id' => Auth::id()]);
            $wishlistItem = WishlistItem::where('wishlist_id', $wishlist->id)
                ->where('product_id', $productId)
                ->first();

            if ($wishlistItem) {
                $wishlistItem->delete();
                $isWishlisted = false;
            } else {
                WishlistItem::create([
                    'wishlist_id' => $wishlist->id,
                    'product_id' => $productId
                ]);
                $isWishlisted = true;
            }

            $count = $wishlist->items->count();
        } else {
            // Session storage for guests
            $wishlist = session()->get('wishlist', []);

            if (in_array($productId, $wishlist)) {
                $wishlist = array_diff($wishlist, [$productId]);
                $isWishlisted = false;
            } else {
                $wishlist[] = $productId;
                $isWishlisted = true;
            }

            session()->put('wishlist', $wishlist);
            $count = count($wishlist);
        }

        return response()->json([
            'isWishlisted' => $isWishlisted,
            'count' => $count
        ]);
    }

    /**
     * Remove product from wishlist
     */
    public function remove(Request $request)
    {
        $productId = $request->input('product_id');

        if (Auth::check()) {
            $wishlist = Wishlist::where('user_id', Auth::id())->first();
            if ($wishlist) {
                WishlistItem::where('wishlist_id', $wishlist->id)
                    ->where('product_id', $productId)
                    ->delete();

                $count = $wishlist->items->count();
            }
        } else {
            $wishlist = session()->get('wishlist', []);
            $wishlist = array_diff($wishlist, [$productId]);
            session()->put('wishlist', $wishlist);
            $count = count($wishlist);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => 'Product removed from wishlist',
                'count' => $count ?? 0,
            ]);
        }

        return back()->with('success', 'Product removed from wishlist');
    }

    /**
     * Move wishlist item to cart
     */
    public function moveToCart(Request $request)
    {
        $productId = $request->input('product_id');

        // Remove from wishlist
        $this->remove($request);

        // Add to cart
        $cartController = new \App\Http\Controllers\CartController();
        return $cartController->addToCart($request);
    }
}
