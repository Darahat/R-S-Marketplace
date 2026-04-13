<?php

namespace App\Repositories;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;

class PaymentProcessRepository
{
    public function getCartWithItems(int $userId): ?Cart
    {
        return Cart::where('user_id', $userId)->with('items.product')->first();
    }

    public function findProduct(int $productId): ?Product
    {
        return Product::find($productId);
    }

    public function findAddressForUser(int $addressId, int $userId): ?Address
    {
        return Address::where('id', $addressId)
            ->where('user_id', $userId)
            ->first();
    }

    public function updateOrderStripeSession(Order $order, string $sessionId): bool
    {
        return $order->update(['stripe_session_id' => $sessionId]);
    }

    public function createPayment(array $data): Payment
    {
        return Payment::create($data);
    }

    public function saveUserStripeCustomerId(User $user, string $stripeCustomerId): bool
    {
        $user->stripe_customer_id = $stripeCustomerId;
        return $user->save();
    }

    public function getCartForUser(int $userId): ?Cart
    {
        return Cart::where('user_id', $userId)->first();
    }

    public function clearCartItems(int $cartId): int
    {
        return CartItem::where('cart_id', $cartId)->delete();
    }

    public function saveOrder(Order $order): bool
    {
        return $order->save();
    }
}
