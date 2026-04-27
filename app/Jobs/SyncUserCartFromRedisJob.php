<?php

namespace App\Jobs;

use App\Repositories\CartRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Redis;

class SyncUserCartFromRedisJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [5, 15, 30];

    public function __construct(public int $userId)
    {
    }

    public function handle(CartRepository $repo): void
    {
        $payload = Redis::get("cart:user:{$this->userId}");
        if (!is_string($payload) || $payload === '') {
            return;
        }

        $items = json_decode($payload, true);

        if (!is_array($items)) {
            return;
        }

        $cart = $repo->firstOrCreateCartByUser($this->userId);
        $repo->clearCartItems($cart->id);

        foreach ($items as $item) {
            $productId = (int) ($item['id'] ?? 0);
            $quantity = max(1, (int) ($item['quantity'] ?? 1));

            if ($productId <= 0) {
                continue;
            }

            $product = $repo->findProduct($productId);
            if (!$product) {
                continue;
            }

            $repo->createCartItem([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => (float) ($item['price'] ?? $product->price),
            ]);
        }
    }
}
