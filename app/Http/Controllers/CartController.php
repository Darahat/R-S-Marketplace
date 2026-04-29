<?php

// app/Http/Controllers/CartController.php
namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Http\Requests\RemoveFromCartRequest;
use App\Http\Requests\UpdateCartRequest;
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

    public function cartRefresh()
    {
        $cart = $this->getCartItems();

        $totalPriceAmount = 0;
        $totalItemCount = 0;

        $html = view('frontend_view.components.cards.cartDropdown',
            compact('totalPriceAmount', 'totalItemCount', 'cart')
        )->render();

        return response($html)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function addToCart(CartRequest $request)
    {
       $validated = $request->validated();
       $cart = $this->cartService->addToCart($validated['product_id'], $validated['quantity']);

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


    public function update(UpdateCartRequest $request)
    {
        $validated = $request->validated();
        $updatedCart = $this->cartService->update((int) $validated['itemId'], (int) $validated['quantity']);


        return response()->json([
            'message' => 'Cart updated successfully',
            'total' => $updatedCart['total'] ?? 0,
            'totalQuantity' => $updatedCart['totalQuantity'] ?? 0
        ]);
    }

    public function remove(RemoveFromCartRequest $request)
    {
         $cart = $this->cartService->remove((int) $request->validated()['item']);

    if ($request->ajax()) {
            return response()->json([
                'success' => 'Product removed from cart',
                'totalQuantity' => $cart['totalQuantity'] ?? 0,
            ]);
        }

        return back()->with('success', 'Product removed from cart');
    }

public function getCartItemsJson()
{
    $cartItems = $this->cartService->getCartItems();

    return response()->json([
        'items' => array_values($cartItems), // Convert to array values for JS
        'total' => $this->cartService->calculateTotal($cartItems),
        'totalQuantity' => collect($cartItems)->sum('quantity'),
    ]);
}
}
