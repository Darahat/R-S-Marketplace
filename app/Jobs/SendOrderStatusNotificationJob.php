<?php

namespace App\Jobs;
use App\Notifications\OrderStatusChangedNotification;
use App\Repositories\OrderRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendOrderStatusNotificationJob implements ShouldQueue
{
    use Queueable;
    public int $tries = 3;
    public array $backoff = [10,30,60];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $orderId,
        public string $oldStatus,
        public string $newStatus,
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(OrderRepository $orderRepository): void
    {
        $order = $orderRepository->findWithUserById($this->orderId);

        if (!$order) {
            Log::warning('SendOrderStatusNotificationJob: order not found', [
                'order_id' => $this->orderId,
            ]);
            return;
        }

        $user = $order->user;

        if(!$user){
            Log::warning('SendOrderStatusNotificationJob: No user Found', [
                'order_id' => $order->id,

            ]);
            return;
        }
        // This sends via database + broadcast (as defined in toArray/toBroadcast)
        $user->notify(new OrderStatusChangedNotification(
            $order,
            $this->oldStatus,
            $this->newStatus,
        ));
          Log::info('Order status notification sent', [
            'order_id' => $order->id,
            'user_id' => $user->id,
            'new_status' => $this->newStatus,
        ]);
    }
    public function failed(\Throwable $e): void{
         Log::error('SendOrderStatusNotificationJob FAILED', [
            'order_id' => $this->orderId,
            'error' => $e->getMessage(),
        ]);
    }
}
