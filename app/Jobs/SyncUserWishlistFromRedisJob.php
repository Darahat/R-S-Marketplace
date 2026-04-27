<?php

namespace App\Jobs;

use App\Repositories\WishlistRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Redis;

class SyncUserWishlistFromRedisJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [5, 15, 30];

    public function __construct(public int $userId)
    {
    }

    public function handle(WishlistRepository $repo): void
    {
        $payload = Redis::get("wishlist:user:{$this->userId}");
        if (!is_string($payload) || $payload === '') {
            return;
        }

        $items = json_decode($payload, true);

        if (!is_array($items)) {
            return;
        }

        $wishlist = $repo->firstOrCreateForUser($this->userId);
        $repo->clearItems($wishlist->id);

        foreach ($items as $item) {
            $productId = (int) ($item['id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }

            if (!$repo->findProduct($productId)) {
                continue;
            }

            $repo->firstOrCreateItem($wishlist->id, $productId);
        }
    }
}
