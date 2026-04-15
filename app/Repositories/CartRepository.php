<?php
namespace App\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Collection;

class CartRepository
{
    public function findProduct(int $productId): ?Product
    {
        return Product::find($productId);
    }

    public function firstOrCreateCartByUser(int $userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    public function getCartForUser(int $userId): ?Cart
    {
        return Cart::where('user_id', $userId)->first();
    }

    public function getCartItemsWithProduct(int $cartId): Collection
    {
        return CartItem::where('cart_id', $cartId)->with('product')->get();
    }

    public function getCartItem(int $cartId, int $productId): ?CartItem
    {
        return CartItem::where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();
    }

    public function createCartItem(array $data): CartItem
    {
        return CartItem::create($data);
    }

    public function saveCartItem(CartItem $cartItem): bool
    {
        return $cartItem->save();
    }

    public function deleteCartItemByProduct(int $cartId, int $productId): int
    {
        return CartItem::where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->delete();
    }

    public function sumCartQuantity(int $cartId): int
    {
        return (int) CartItem::where('cart_id', $cartId)->sum('quantity');
    }

    public function countCartItems(int $cartId): int
    {
        return (int) CartItem::where('cart_id', $cartId)->count();
    }

    public function syncGuestCartItems(int $userId, array $guestCart): void
    {
        $cart = $this->firstOrCreateCartByUser($userId);

        foreach ($guestCart as $productId => $item) {
            $productId = (int) $productId;
            $quantity = (int) ($item['quantity'] ?? 1);
            $price = (float) ($item['price'] ?? 0);

            $cartItem = $this->getCartItem($cart->id, $productId);

            if ($cartItem) {
                $cartItem->quantity += $quantity;
                $this->saveCartItem($cartItem);
                continue;
            }

            $this->createCartItem([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
            ]);
        }
    }

}
