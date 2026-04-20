<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Jobs\UpdateProductSalesMetricsJob;
use Illuminate\Support\Facades\Log;

class StockManagementService
{
    public function __construct(protected ProductRepository $repo)
    {
    }

    /**
     * Validate that all cart items have sufficient stock available
     *
     * @param array $cartItems
     * @throws \Exception if any product is out of stock
     */
    public function validateAvailability(array $cartItems): void
    {
        foreach ($cartItems as $item) {
            $product = $this->repo->findProduct($item['id']);
            if (!$product || $product->stock < $item['quantity']) {
                throw new \Exception("{$item['name']} is no longer available in the requested quantity");
            }
        }
    }

    /**
     * Decrement stock for cart items and dispatch sales metrics job
     *
     * @param array $cartItems
     */
    public function decrementStock(array $cartItems): void
    {
        $metricsPayload = [];

        foreach ($cartItems as $item) {
            $product = $this->repo->findProduct((int) $item['id']);
            if ($product) {
                $product->stock -= $item['quantity'];
                $product->save();

                $metricsPayload[] = [
                    'product_id' => (int) $item['id'],
                    'quantity' => (int) $item['quantity'],
                ];
            }
        }

        if (!empty($metricsPayload)) {
            UpdateProductSalesMetricsJob::dispatch($metricsPayload)->onQueue('default');
        }
    }

    /**
     * Update stock for a single product (add or remove)
     * Used for admin stock adjustments
     *
     * @param int $productId
     * @param int $quantity Positive to add, negative to remove
     * @return bool
     */
    public function adjustStock(int $productId, int $quantity): bool
    {
        $product = $this->repo->findProduct($productId);

        if (!$product) {
            return false;
        }

        $newStock = $product->stock + $quantity;

        if ($newStock < 0) {
            Log::warning('Attempted to set negative stock', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'current_stock' => $product->stock
            ]);
            return false;
        }

        return $this->repo->updateProduct($productId, [
            'stock' => $newStock
        ]);
    }
}
