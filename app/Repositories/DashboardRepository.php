<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class DashboardRepository
{
    public function countAllOrders(): int
    {
        return Order::count();
    }

    public function countTodayOrders(): int
    {
        return Order::whereDate('created_at', Carbon::today())->count();
    }

    public function countOrdersBetween(Carbon $from, Carbon $to): int
    {
        return Order::whereBetween('created_at', [$from, $to])->count();
    }

    public function sumPaidRevenue(): float
    {
        return (float) Order::where('payment_status', 'paid')->sum('total_amount');
    }

    public function sumPaidRevenueToday(): float
    {
        return (float) Order::where('payment_status', 'paid')
            ->whereDate('created_at', Carbon::today())
            ->sum('total_amount');
    }

    public function sumPaidRevenueBetween(Carbon $from, Carbon $to): float
    {
        return (float) Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$from, $to])
            ->sum('total_amount');
    }

    public function sumProfitForPaidOrders(?Carbon $from = null, ?Carbon $to = null): float
    {
        $query = OrderItem::whereHas('order', fn($q) => $q->where('payment_status', 'paid'))
            ->with('product');

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        return (float) $query->get()
            ->sum(fn($item) => ($item->price - $item->product->purchase_price) * $item->quantity);
    }

    public function countAllProducts(): int
    {
        return Product::count();
    }

    public function countLowStockProducts(int $threshold = 10): int
    {
        return Product::where('stock', '<', $threshold)->count();
    }

    public function countOutOfStockProducts(): int
    {
        return Product::where('stock', '=', 0)->count();
    }

    public function countOrdersByStatus(string $status): int
    {
        return Order::where('order_status', $status)->count();
    }

    public function countOrdersByPaymentStatus(string $status): int
    {
        return Order::where('payment_status', $status)->count();
    }

    public function getTopProductsLastDays(int $days = 30, int $limit = 5): Collection
    {
        return OrderItem::whereHas('order', fn($q) => $q->where('created_at', '>=', Carbon::now()->subDays($days)))
            ->with('product:id,name,image,price')
            ->get()
            ->groupBy('product_id')
            ->map(function ($items) {
                $product = $items->first()->product;

                return (object) [
                    'id' => $product?->id,
                    'name' => $product?->name ?? 'Unknown Product',
                    'image' => $product?->image,
                    'total_sold' => $items->sum('quantity'),
                    'total_revenue' => $items->sum(fn($item) => $item->price * $item->quantity),
                ];
            })
            ->sortByDesc('total_sold')
            ->take($limit)
            ->values();
    }

    public function getRecentOrders(int $limit = 10): EloquentCollection
    {
        return Order::with(['user', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getCustomerStats(int $userId): ?object
    {
        return Order::where('user_id', $userId)
            ->selectRaw("COUNT(*) as total_order_count,
                SUM(CASE WHEN order_status = 'completed' THEN 1 ELSE 0 END) as completed_order_count,
                SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_order_count,
                SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_order_count,
                SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_order_count,
                SUM(CASE WHEN order_status = 'shipped' THEN 1 ELSE 0 END) as shipped_order_count,
                SUM(CASE WHEN order_status = 'returned' THEN 1 ELSE 0 END) as returned_order_count,
                COALESCE(SUM(total_amount), 0) as total_spent,
                COALESCE(SUM(discount), 0) as total_discount")
            ->first();
    }

    public function getCustomerRecentOrders(int $userId, int $days = 1): EloquentCollection
    {
        return Order::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->get();
    }

    public function getWishlistWithItems(int $userId): ?Wishlist
    {
        return Wishlist::where('user_id', $userId)
            ->with('items.product')
            ->first();
    }

    public function getCartWithItems(int $userId): ?Cart
    {
        return Cart::where('user_id', $userId)
            ->with('items.product')
            ->first();
    }

    public function findCustomerOrderDetailsOrFail(string $orderNumber, int $userId): Order
    {
        return Order::where('order_number', $orderNumber)
            ->where('user_id', $userId)
            ->with(['address.district', 'address.upazila', 'address.union', 'items.product', 'payments'])
            ->firstOrFail();
    }

    public function getCustomerOrderHistory(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Order::with(['items.product'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function countOrdersByUser(int $userId): int
    {
        return Order::where('user_id', $userId)->count();
    }

    public function sumPaidSpentByUser(int $userId): float
    {
        return (float) Order::where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->sum('total_amount');
    }

    public function countWishlistItemsByUser(int $userId): int
    {
        return Wishlist::where('user_id', $userId)
            ->withCount('items')
            ->first()?->items_count ?? 0;
    }
}
