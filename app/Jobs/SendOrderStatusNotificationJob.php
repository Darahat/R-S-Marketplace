<?php

namespace App\Jobs;
use App\Models\Order;
use App\Notifications\OrderStatusChangedNotification;
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
        public Order $order,
        public string $oldStatus,
        public string $newStatus,
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->order->user;

        if(!$user){
            Log::warning('SendOrderStatusNotificationJob: No user Found', [
                'order_id' =>$this->order->id,

            ]);
            return;
        }
        // This sends via database + broadcast (as defined in toArray/toBroadcast)
        $user->notify(new OrderStatusChangedNotification(
            $this->order,
            $this->oldStatus,
            $this->newStatus,
        ));
          Log::info('Order status notification sent', [
            'order_id' => $this->order->id,
            'user_id' => $user->id,
            'new_status' => $this->newStatus,
        ]);
    }
    public function failed(\Throwable $e): void{
         Log::error('SendOrderStatusNotificationJob FAILED', [
            'order_id' => $this->order->id,
            'error' => $e->getMessage(),
        ]);
    }
}
