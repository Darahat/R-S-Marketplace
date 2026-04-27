<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Redis;

class SyncGuestWishlistSessionFromRedisJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [5, 15, 30];

    public function __construct(public string $sessionId)
    {
    }

    public function handle(): void
    {
        $payload = Redis::get("wishlist:guest:{$this->sessionId}");
        if (!is_string($payload) || $payload === '') {
            return;
        }

        $items = json_decode($payload, true);
        if (!is_array($items)) {
            return;
        }

        $wishlistProductIds = [];
        foreach ($items as $item) {
            $productId = (int) ($item['id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }
            $wishlistProductIds[] = $productId;
        }

        $store = app('session')->driver();
        $store->setId($this->sessionId);
        $store->start();
        $store->put('wishlist', array_values(array_unique($wishlistProductIds)));
        $store->save();
    }
}
