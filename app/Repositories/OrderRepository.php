<?php

namespace App\Repositories;

use App\Models\Order;
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
}
