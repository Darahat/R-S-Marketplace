<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class UpdateProductSalesMetricsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    /**
     * @param array<int, array{product_id:int, quantity:int}> $items
     */
    public function __construct(public array $items)
    {
    }

    public function handle(): void
    {
        foreach ($this->items as $item) {
            $product = Product::find($item['product_id']);

            if (!$product) {
                continue;
            }

            $quantity = max(1, (int) $item['quantity']);
            $product->sold_count = (int) ($product->sold_count ?? 0) + $quantity;
            $product->save();

            // Keep this lightweight: queue handles metric updates and low-stock observability.
            if ((int) $product->stock <= 5) {
                Log::warning('Low stock threshold reached', [
                    'product_id' => $product->id,
                    'stock' => $product->stock,
                ]);
            }
        }
    }
}
