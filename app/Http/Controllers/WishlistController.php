<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Models\Product;
use App\Services\WishlistService;
class WishlistController extends Controller
{
    protected $siteTitle;

    function __construct(protected WishlistService $wishListService)
    {
        $this->siteTitle = 'R&SMarketPlace | ';
    }

    /**
     * Get wishlist items based on authentication status
     */


public function getCount()
{
    return $this->wishListService->getWishlistCount();
}

public function syncGuestWishlist($id){
     return $this->wishListService->syncGuestWishlist($id);
}
    /**
     * Display wishlist page (frontend)
     */
    public function view()
    {
        $wishlistItems = $this->wishListService->getWishlistItems();
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
     * Display wishlist page (customer panel)
     */
    public function customerWishlist()
    {
        $wishlistItems = $this->wishListService->getWishlistItems();

        return view('backend_panel_view_customer.pages.wishlist', [
            'wishlistItems' => $wishlistItems,
            'page_title' => $this->siteTitle . 'Wishlist',
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
        $wishlist = $this->wishListService->wishlistToggle($productId);

        return response()->json([
            'is_wishlisted' => $wishlist['is_wishlisted'],
            'count' => $wishlist['count'],
            'success' => 'Product added to wishlist successfully'
        ]);
    }

    /**
     * Remove product from wishlist
     */
    public function remove(Request $request)
    {


        $productId = $request->input('product_id');

        $count = $this->wishListService->removeWishlistProduct($productId);

        if ($request->ajax()) {
            return response()->json([
                'success' => 'Product removed from wishlist',
                'count' => $count ?? 0,
            ]);
        }

        return back()->with('success', 'Product removed from wishlist');
    }


    public function moveToCart(Request $request){
        $productId = $request->input('product_id');
        return $this->wishListService->wishlistMoveToCart($productId);



    }
}
