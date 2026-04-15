<?php
namespace App\Services;

use App\Repositories\CartRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartService
{
    public function __construct(protected CartRepository $repo)
    {

    }

    public function syncGuestCart(int $id): void
    {
        if (session()->has('cart')) {
            $guestCart = session('cart', []);
            $this->repo->syncGuestCartItems($id, $guestCart);
            session()->forget('cart');
        }
    }

    public function getCartItems(): array
    {
        if (Auth::check()) {
            $cart = $this->repo->firstOrCreateCartByUser((int) Auth::id());

            return $this->repo->getCartItemsWithProduct($cart->id)->map(function ($item) {
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

    public function addToCart(string $productId, string $quantity): array
    {
        Log::info($productId);
        Log::info($quantity);

        $productId = (int) $productId;
        $quantity = (int) $quantity;
        $product = $this->repo->findProduct($productId);

        if (!$product) {
            return [
                'error' => 'Product not found.',
                'totalQuantity' => 0,
                'cartCount' => 0,
            ];
        }

        if (Auth::check()) {
            // Database storage for logged-in users
            $cart = $this->repo->firstOrCreateCartByUser((int) Auth::id());
            $cartItem = $this->repo->getCartItem($cart->id, $productId);

            if ($cartItem) {
                $cartItem->quantity += $quantity;
                $this->repo->saveCartItem($cartItem);
            } else {
                $this->repo->createCartItem([
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product->price
                ]);
            }

            $totalQuantity = $this->repo->sumCartQuantity($cart->id);
            $cartCount = $this->repo->countCartItems($cart->id);
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

    public function update(int $itemId, int $quantity): array
    {
        $total = 0;
        $totalQuantity = 0;

        if (Auth::check()) {
            $cart = $this->repo->getCartForUser((int) Auth::id());
            if ($cart) {
                $cartItem = $this->repo->getCartItem($cart->id,$itemId);

                if ($cartItem) {
                    $cartItem->quantity = $quantity;
                    $this->repo->saveCartItem($cartItem);
                }

                $cartItems = $this->getCartItems();
                $total = $this->calculateTotal($cartItems);
                $totalQuantity = $this->repo->sumCartQuantity($cart->id);
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

    public function remove(int $itemId): array
    {
        $total = 0;
        $totalQuantity = 0;

        if (Auth::check()) {
            $cart = $this->repo->getCartForUser((int) Auth::id());
            if ($cart) {
                $this->repo->deleteCartItemByProduct($cart->id, $itemId);
                $cartItems = $this->getCartItems();
                $total = $this->calculateTotal($cartItems);
                $totalQuantity = $this->repo->sumCartQuantity($cart->id);
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




