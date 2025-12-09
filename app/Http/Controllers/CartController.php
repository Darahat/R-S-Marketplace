<?php

// app/Http/Controllers/CartController.php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

 use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $siteTitle;
    function __construct()
    {
        $this->siteTitle = 'R&SMarketPlace | ';
    }
    public function view()
    {
        // Get cart items (example using session)
        $cartItems = session('cart', []);

        // Or if using database:
        // $cartItems = auth()->user()->cartItems()->with('product')->get();
        $data = array();
        $data['title'] = $this->siteTitle . 'Home';
        $data['page'] = 'home';
        return view('frontend_view.pages.cart.view', [
            'cartItems' => $cartItems,
            'total' => $this->calculateTotal($cartItems),
            'data' => $data,
        ]);
    }
    public function refreshView()
    {
        $cartItems = session('cart', []);
        $total = $this->calculateTotal($cartItems);

        return view('frontend_view.pages.cart.cartItems', compact('cartItems', 'total'))->render();
    }

    protected function calculateTotal($items)
    {
        return array_reduce($items, function($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);
    }
    public function addToCart(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);

        $product = DB::table('products')->where('id', $productId)->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found.'], 404);
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            // Increment quantity if already in cart
            $cart[$productId]['quantity'] += $quantity;
        } else {
            // Add new item
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'image' => $product->image_url
            ];
        }

        session()->put('cart', $cart);
        $totalQuantity = collect($cart)->sum('quantity');

        // If using AJAX, respond with JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => 'Product added to cart!',
                'cart' => count($cart),
                'cartQuantity' => $totalQuantity,
            ]);
        }

        return back()->with('success', 'Product added to cart!');
    }


    public function update(Request $request)
    {
        $itemId = $request->itemId;
        $quantity = (int) $request->quantity;

        $cart = session()->get('cart', []);

        if (isset($cart[$itemId])) {
            $cart[$itemId]['quantity'] = $quantity;
            session()->put('cart', $cart);
        }

        $total = $this->calculateTotal($cart);
        $totalQuantity = collect($cart)->sum('quantity');

        return response()->json([
            'message' => 'Cart updated successfully',
            'total' => $total,
            'totalQuantity' => $totalQuantity
        ]);
    }



    public function remove(Request $request)
    {
        $cart = session()->get('cart', []);
        $itemId = $request->input('item');

        if (isset($cart[$itemId])) {
            unset($cart[$itemId]);
            session()->put('cart', $cart);
        }

        $totalQuantity = collect($cart)->sum('quantity');

        if ($request->ajax()) {
            return response()->json([
                'success' => 'Product removed from cart',
                'totalQuantity' => $totalQuantity,
            ]);
        }

        return back()->with('success', 'Product removed from cart');
    }

    public function wishlist(Request $request)
    {
        $wishlist = session()->get('wishlist', []);
        $productId = $request->product_id;

        if (in_array($productId, $wishlist)) {
            $wishlist = array_diff($wishlist, [$productId]);
            $isWishlisted = false;
        } else {
            $wishlist[] = $productId;
            $isWishlisted = true;
        }

        session()->put('wishlist', $wishlist);

        return response()->json([
            'isWishlisted' => $isWishlisted,
            'count' => count($wishlist)
        ]);
    }



}
