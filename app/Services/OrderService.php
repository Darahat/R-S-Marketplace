<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Jobs\SendOrderStatusNotificationJob;
use App\Jobs\SendPaymentStatusNotificationJob;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrderService
{
    use AuthorizesRequests;

    public function __construct(protected OrderRepository $repo)
    {
    }

    public function getOrdersService(array $filters)
    {
        return $this->repo->getFilteredOrders($filters);
    }

    public function findDetailedByIdOrFail(int $id)
    {
        return $this->repo->findDetailedByIdOrFail($id);
    }

    public function updateStatusService(array $validator, int $id): array
    {
        $order = $this->repo->findOrFail($id);
        $oldStatus = $order->order_status;
        $order->order_status = $validator['status'];

        // Set timestamps for certain statuses
        if ($order->order_status == 'shipped' && !$order->shipped_at) {
            $order->shipped_at = now();
        }

        if ($order->order_status == 'delivered' && !$order->delivered_at) {
            $order->delivered_at = now();
        }

        $this->repo->save($order);
        SendOrderStatusNotificationJob::dispatch($order->id, $oldStatus, $order->order_status)->onQueue('default');

        return [
            'order_status' => $order->order_status,
            'oldStatus' => $oldStatus,
        ];
    }

    public function updatePaymentStatusService(array $validator, int $id): array
    {
        $order = $this->repo->findOrFail($id);
        $oldStatus = $order->payment_status;
        $order->payment_status = $validator['payment_status'];

        $this->repo->save($order);

        if ($oldStatus !== $order->payment_status) {
            SendPaymentStatusNotificationJob::dispatch($order->id, $oldStatus, $order->payment_status)->onQueue('default');
        }

        return [
            'payment_status' => $order->payment_status,
            'oldStatus' => $oldStatus,
        ];
    }

    public function updateNotesService(array $validator, int $id): bool
    {
        $order = $this->repo->findOrFail($id);
        $order->notes = $validator['notes'] ?? null;

        return $this->repo->save($order);
    }

    public function getStatisticsService(): array
    {
        return $this->repo->getStatistics();
    }

    /**
     * Create a new order with items
     */
    public function createOrder(int $userId, array $orderData, array $cartItems): Order
    {
        $order = $this->repo->createOrder([
            'user_id' => $userId,
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'address_id' => $orderData['address_id'],
            'order_status' => $orderData['order_status'] ?? 'Processing',
            'total_amount' => $orderData['total_amount'],
            'payment_method' => $orderData['payment_method'],
            'payment_status' => $orderData['payment_status'] ?? 'pending',
            'notes' => $orderData['notes'] ?? '',
        ]);

        foreach ($cartItems as $item) {
            $itemTotal = $item['price'] * $item['quantity'];
            $this->repo->createOrderItem([
                'order_id' => $order->id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $itemTotal,
            ]);
        }

        return $order;
    }

    /**
     * Get orders with "to_pay" status for a user
     */
    public function getToPayOrders(int $userId, int $perPage = 10)
    {
        return $this->repo->getToPayOrdersByUser($userId, $perPage);
    }

    /**
     * Find a "to_pay" order by order number for payment
     */
    public function findOrderForPayment(string $orderNumber, int $userId): ?Order
    {
        return $this->repo->findToPayOrderByNumber($orderNumber, $userId);
    }

    /**
     * Find user order by order number
     */
    public function findUserOrderByNumber(string $orderNumber, int $userId): ?Order
    {
        return $this->repo->findUserOrderByNumber($orderNumber, $userId);
    }
}
