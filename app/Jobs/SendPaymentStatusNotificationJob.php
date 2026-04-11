<?php

namespace App\Jobs;

use App\Models\Order;
use App\Notifications\PaymentStatusChangedNotification;
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
        public Order $order,
        public string $oldStatus,
        public string $newStatus,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->order->user;

        if (!$user) {
            Log::warning('SendPaymentStatusNotificationJob: No user found', [
                'order_id' => $this->order->id,
            ]);
            return;
        }

        $user->notify(new PaymentStatusChangedNotification(
            $this->order,
            $this->oldStatus,
            $this->newStatus,
        ));

        Log::info('Payment status notification sent', [
            'order_id' => $this->order->id,
            'user_id' => $user->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendPaymentStatusNotificationJob FAILED', [
            'order_id' => $this->order->id,
            'error' => $e->getMessage(),
        ]);
    }
}
