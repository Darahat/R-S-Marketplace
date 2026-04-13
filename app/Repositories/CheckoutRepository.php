<?php

namespace App\Repositories;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\UserPaymentMethod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CheckoutRepository
{
    public function getUserCartWithItems(int $userId): ?Cart
    {
        return Cart::where('user_id', $userId)->with('items.product')->first();
    }

    public function findUserAddress(int $addressId, int $userId): ?Address
    {
        return Address::where('id', $addressId)
            ->where('user_id', $userId)
            ->first();
    }

    public function findUserAddressWithRelations(int $addressId, int $userId): ?Address
    {
        return Address::with(['district', 'upazila', 'union'])
            ->where('id', $addressId)
            ->where('user_id', $userId)
            ->first();
    }

    public function getUserAddresses(int $userId): Collection
    {
        return Address::with('district:id,name', 'upazila:id,name', 'union:id,name')
            ->where('addresses.user_id', $userId)
            ->orderByDesc('addresses.is_default')
            ->orderByDesc('addresses.id')
            ->get();
    }

    public function getSavedPaymentMethods(int $userId): Collection
    {
        return UserPaymentMethod::where('user_id', $userId)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findProductById(int $productId): ?Product
    {
        return Product::find($productId);
    }

    public function saveProduct(Product $product): bool
    {
        return $product->save();
    }

    public function createOrder(array $attributes): Order
    {
        return Order::create($attributes);
    }

    public function createOrderItem(array $attributes): OrderItem
    {
        return OrderItem::create($attributes);
    }

    public function saveOrder(Order $order): bool
    {
        return $order->save();
    }

    public function findUserOrderByNumber(string $orderNumber, int $userId): ?Order
    {
        return Order::where('order_number', $orderNumber)
            ->where('user_id', $userId)
            ->with(['items.product', 'address.district', 'address.upazila', 'address.union'])
            ->first();
    }

    public function savedPaymentMethodExists(int $userId, string $paymentMethodId): bool
    {
        return UserPaymentMethod::where('user_id', $userId)
            ->where('stripe_payment_method_id', $paymentMethodId)
            ->exists();
    }

    public function countSavedPaymentMethods(int $userId): int
    {
        return UserPaymentMethod::where('user_id', $userId)->count();
    }

    public function createSavedPaymentMethod(array $attributes): UserPaymentMethod
    {
        return UserPaymentMethod::create($attributes);
    }

    public function getToPayOrdersByUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Order::where('user_id', $userId)
            ->where('order_status', 'to_pay')
            ->with(['items.product', 'address.district', 'address.upazila', 'address.union'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findToPayOrderByNumber(string $orderNumber, int $userId): ?Order
    {
        return Order::where('order_number', $orderNumber)
            ->where('user_id', $userId)
            ->where('order_status', 'to_pay')
            ->first();
    }
}
