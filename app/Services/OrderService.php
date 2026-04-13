<?php

namespace App\Services;

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
        SendOrderStatusNotificationJob::dispatch($order, $oldStatus, $order->order_status)->onQueue('default');

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
            SendPaymentStatusNotificationJob::dispatch($order, $oldStatus, $order->payment_status)->onQueue('default');
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
}
