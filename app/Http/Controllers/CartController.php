<?php

// app/Http/Controllers/CartController.php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $siteTitle;
    function __construct()
    {
        $this->siteTitle = 'R&SMarketPlace | ';
    }

    /**
     * Get cart items based on authentication status
     */
    protected function getCartItems()
    {
        if (Auth::check()) {
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
            return $cart->items()->with('product')->get()->map(function ($item) {
                return [
                    'id' => $item->product_id,
                    'name' => $item->product->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'image' => $item->product->image
                ];
            })->toArray();
        }

        return session('cart', []);
    }

    /**
     * Sync guest cart to database when user logs in
     */
    public function syncGuestCart()
    {
        if (Auth::check() && session()->has('cart')) {
            $guestCart = session('cart', []);
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

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

    public function view()
    {
        $cartItems = $this->getCartItems();
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
        $cartItems = $this->getCartItems();
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

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['error' => 'Product not found.'], 404);
        }

        if (Auth::check()) {
            // Database storage for logged-in users
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $productId)
                ->first();

            if ($cartItem) {
                $cartItem->quantity += $quantity;
                $cartItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product->price
                ]);
            }

            $totalQuantity = $cart->items->sum('quantity');
            $cartCount = $cart->items->count();
        } else {
            // Session storage for guests
            $cart = session()->get('cart', []);

            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] += $quantity;
            } else {
                $cart[$productId] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'image' => $product->image
                ];
            }

            session()->put('cart', $cart);
            $totalQuantity = collect($cart)->sum('quantity');
            $cartCount = count($cart);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => 'Product added to cart!',
                'cart' => $cartCount,
                'cartQuantity' => $totalQuantity,
            ]);
        }

        return back()->with('success', 'Product added to cart!');
    }


    public function update(Request $request)
    {
        $itemId = $request->itemId;
        $quantity = (int) $request->quantity;

        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
            if ($cart) {
                $cartItem = CartItem::where('cart_id', $cart->id)
                    ->where('product_id', $itemId)
                    ->first();

                if ($cartItem) {
                    $cartItem->quantity = $quantity;
                    $cartItem->save();
                }

                $cartItems = $this->getCartItems();
                $total = $this->calculateTotal($cartItems);
                $totalQuantity = $cart->items->sum('quantity');
            }
        } else {
            $cart = session()->get('cart', []);

            if (isset($cart[$itemId])) {
                $cart[$itemId]['quantity'] = $quantity;
                session()->put('cart', $cart);
            }

            $total = $this->calculateTotal($cart);
            $totalQuantity = collect($cart)->sum('quantity');
        }

        return response()->json([
            'message' => 'Cart updated successfully',
            'total' => $total ?? 0,
            'totalQuantity' => $totalQuantity ?? 0
        ]);
    }

    public function remove(Request $request)
    {
        $itemId = $request->input('item');

        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
            if ($cart) {
                CartItem::where('cart_id', $cart->id)
                    ->where('product_id', $itemId)
                    ->delete();

                $totalQuantity = $cart->items->sum('quantity');
            }
        } else {
            $cart = session()->get('cart', []);

            if (isset($cart[$itemId])) {
                unset($cart[$itemId]);
                session()->put('cart', $cart);
            }

            $totalQuantity = collect($cart)->sum('quantity');
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => 'Product removed from cart',
                'totalQuantity' => $totalQuantity ?? 0,
            ]);
        }

        return back()->with('success', 'Product removed from cart');
    }
}
