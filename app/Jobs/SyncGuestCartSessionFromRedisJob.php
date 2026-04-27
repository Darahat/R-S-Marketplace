<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Redis;

class SyncGuestCartSessionFromRedisJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [5, 15, 30];

    public function __construct(public string $sessionId)
    {
    }

    public function handle(): void
    {
        $payload = Redis::get("cart:guest:{$this->sessionId}");
        if (!is_string($payload) || $payload === '') {
            return;
        }

        $items = json_decode($payload, true);
        if (!is_array($items)) {
            return;
        }

        $sessionCart = [];
        foreach ($items as $item) {
            $productId = (int) ($item['id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }

            $sessionCart[$productId] = [
                'id' => $productId,
                'name' => (string) ($item['name'] ?? ''),
                'price' => (float) ($item['price'] ?? 0),
                'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                'image' => (string) ($item['image'] ?? ''),
            ];
        }

        $store = app('session')->driver();
        $store->setId($this->sessionId);
        $store->start();
        $store->put('cart', $sessionCart);
        $store->save();
    }
}
