<?php

namespace App\Jobs;

use App\Notifications\PaymentStatusChangedNotification;
use App\Repositories\OrderRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendPaymentStatusNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $orderId,
        public string $oldStatus,
        public string $newStatus,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(OrderRepository $orderRepository): void
    {
        $order = $orderRepository->findWithUserById($this->orderId);

        if (!$order) {
            Log::warning('SendPaymentStatusNotificationJob: order not found', [
                'order_id' => $this->orderId,
            ]);
            return;
        }

        $user = $order->user;

        if (!$user) {
            Log::warning('SendPaymentStatusNotificationJob: No user found', [
                'order_id' => $order->id,
            ]);
            return;
        }

        $user->notify(new PaymentStatusChangedNotification(
            $order,
            $this->oldStatus,
            $this->newStatus,
        ));

        Log::info('Payment status notification sent', [
            'order_id' => $order->id,
            'user_id' => $user->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendPaymentStatusNotificationJob FAILED', [
            'order_id' => $this->orderId,
            'error' => $e->getMessage(),
        ]);
    }
}
