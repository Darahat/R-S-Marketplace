<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository
{
    public function getFilteredOrders(array $filters): LengthAwarePaginator
    {
        return Order::with(['user', 'address', 'items.product'])
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%$search%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('name', 'like', "%$search%")
                                ->orWhere('email', 'like', "%$search%");
                        });
                });
            })
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('order_status', $v))
            ->when($filters['payment_status'] ?? null, fn ($q, $v) => $q->where('payment_status', $v))
            ->when($filters['payment_method'] ?? null, fn ($q, $v) => $q->where('payment_method', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate(20);
    }

    public function findOrFail(int $id): Order
    {
        return Order::findOrFail($id);
    }

    public function save(Order $order): bool
    {
        return $order->save();
    }

    public function findDetailedByIdOrFail(int $id): Order
    {
        return Order::with(['user', 'address', 'items.product'])->findOrFail($id);
    }

    public function findWithUserById(int $id): ?Order
    {
        return Order::with('user')->find($id);
    }

    public function getStatistics(): array
    {
        return [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'shipped_orders' => Order::where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total'),
            'pending_payment' => Order::where('payment_status', 'unpaid')->sum('total'),
        ];
    }

    public function createOrder(array $attributes): Order
    {
        return Order::create($attributes);
    }

    public function createOrderItem(array $attributes): OrderItem
    {
        return OrderItem::create($attributes);
    }

    public function findUserOrderByNumber(string $orderNumber, int $userId): ?Order
    {
        return Order::where('order_number', $orderNumber)
            ->where('user_id', $userId)
            ->with(['items.product', 'address.district', 'address.upazila', 'address.union'])
            ->first();
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
