<?php

// app/Http/Controllers/CartController.php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\CartItem;

use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $siteTitle;
    function __construct(protected CartService $cartService)
    {
        $this->siteTitle = 'R&SMarketPlace | ';
    }

    /**
     * Get cart items based on authentication status
     */
    protected function getCartItems()
    {

            return $this->cartService->getCartItems();


    }



    public function view()
    {
        $cartItems = $this->getCartItems();

        $data = array();
        $data['title'] = $this->siteTitle . 'Home';
        $data['page'] = 'home';
        return view('frontend_view.pages.cart.view', [
            'cartItems' => $cartItems,
            'total' => $this->cartService->calculateTotal($cartItems),
            'data' => $data,
        ]);
    }
    public function refreshView()
    {
        $cartItems = $this->getCartItems();
        $total = $this->cartService->calculateTotal($cartItems);

        return view('frontend_view.pages.cart.cartItems', compact('cartItems', 'total'))->render();
    }

public function cartRefresh(){


    // Get cart items based on authentication
     $cart = $this->getCartItems(); // reuse existing service method

    $totalPriceAmount = 0;
    $totalItemCount = 0;

    return view('frontend_view.components.cards.cartDropdown',
        compact('totalPriceAmount', 'totalItemCount', 'cart')
    )->render();
    }

    public function addToCart(Request $request)
    {
       $cart = $this->cartService->addToCart($request->input('product_id'),$request->input('quantity', 1));

       if (isset($cart['error'])) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => $cart['error'],
                ], 404);
            }

            return back()->with('error', $cart['error']);
       }

        if ($request->ajax()) {
            return response()->json([
                'success' => 'Product added to cart!',
                'cart' => $cart['cartCount'],
                'cartQuantity' => $cart['totalQuantity'],
            ]);
        }

        return back()->with('success', 'Product added to cart!');
    }


    public function update(Request $request)
    {

        $updatedCart = $this->cartService->update( $request->itemId,(int) $request->quantity);


        return response()->json([
            'message' => 'Cart updated successfully',
            'total' => $updatedCart['total'] ?? 0,
            'totalQuantity' => $updatedCart['totalQuantity'] ?? 0
        ]);
    }

    public function remove(Request $request)
    {
         $cart = $this->cartService->remove($request->input('item'));

    if ($request->ajax()) {
            return response()->json([
                'success' => 'Product removed from cart',
                'totalQuantity' => $cart['totalQuantity'] ?? 0,
            ]);
        }

        return back()->with('success', 'Product removed from cart');
    }


}
