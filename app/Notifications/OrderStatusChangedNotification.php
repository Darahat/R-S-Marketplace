<?php

namespace App\Notifications;
use App\Models\Order;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class OrderStatusChangedNotification extends Notification
{
    use Queueable;
    public int $tries = 3;
    public array $backoff = [10,30,60];
    /**
     * Create a new notification instance.
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
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    // What to store in the database (for the notification)
    public function toDatabase(object $notifiable): array{
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => $this->getMessage(),
        ];
    }
    /// What to send via WebSocket [instant push to browser]
    public function toBroadcast(object $notifiable): BroadcastMessage{
        return new BroadcastMessage([
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'new_status' => $this->newStatus,
            'message' => $this->getMessage(),
            'time' => now()->diffForHumans(),
        ]);
    }
    private function getMessage(): string{
        return match($this->newStatus){
            'confirmed' => "Your order #{$this->order->order_number} has been confirmed",
            'processing' => "Your order #{$this->order->order_number} is being prepared.",
            'shipped'    => "Your order #{$this->order->order_number} has been shipped!",
            'delivered'  => "Your order #{$this->order->order_number} has been delivered!",
            'cancelled'  => "Your order #{$this->order->order_number} has been cancelled.",
            default      => "Your order #{$this->order->order_number} status changed to {$this->newStatus}.",
            };
    }
}
