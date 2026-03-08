<?php
namespace App\Services;
use App\Models\Cart;
use App\Models\CartItem;
use App\Repositories\CartRepository;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Illuminate\Support\Collection;
class CartService{
  public function __construct(protected CartRepository $repo)
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
    public function getCartItems():array{
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

        return session()->get('cart', []);
    }

    public function addToCart(String $productId,String $quantity):Array {
        $product = Product::find($productId);
        if (Auth::check()) {
            // Database storage for logged-in users
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
            // dd($cart,$productId);
            $cartItem = $this->repo->getCartItem($cart->id, $productId);

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

        return [
    'totalQuantity' => $totalQuantity,
    'cartCount' => $cartCount
];
    }

    public function update(int $itemId, int $quantity){
        if (Auth::check()) {
            $cart = $this->repo->getUserIdFromCart();
            if ($cart) {
                $cartItem = $this->repo->getCartItem($cart->id,$itemId);

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
             return [
    'totalQuantity' => $totalQuantity,
    'total' => $total
];

    }
    public function calculateTotal($items)
    {
        return array_reduce($items, function($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);
    }

    public function remove(int $itemId):Array{


        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
            if ($cart) {
                CartItem::where('cart_id', $cart->id)
                    ->where('product_id', $itemId)
                    ->delete();
                $cartItems = $this->getCartItems();
                $total = $this->calculateTotal($cartItems);
                $totalQuantity = $cart->items->sum('quantity');
            }
        } else {
            $cart = session()->get('cart', []);

            if (isset($cart[$itemId])) {
                unset($cart[$itemId]);
                session()->put('cart', $cart);
            }
            $total = $this->calculateTotal($cart);
            $totalQuantity = collect($cart)->sum('quantity');
        }

    return [
    'totalQuantity' => $totalQuantity,
    'total' => $total
    ];
    }
    }




