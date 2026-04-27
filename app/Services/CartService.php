<?php
namespace App\Services;

use App\Jobs\SyncGuestCartSessionFromRedisJob;
use App\Jobs\SyncUserCartFromRedisJob;
use App\Repositories\CartRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Throwable;

class CartService
{
    private const CART_TTL_SECONDS = 2592000; // 30 days

    public function __construct(protected CartRepository $repo)
    {

    }

    private function cartRedisKey(?int $userId = null): string
    {
        if ($userId) {
            return "cart:user:{$userId}";
        }

        if (session()->isStarted() === false) {
            session()->start();
        }

        return 'cart:guest:' . session()->getId();
    }

    private function getCartFromRedis(?int $userId = null): ?array
    {
        try {
            $payload = Redis::get($this->cartRedisKey($userId));
        } catch (Throwable $e) {
            return null;
        }

        if (!$payload) {
            return null;
        }

        $decoded = json_decode($payload, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function saveCartToRedis(array $cart, ?int $userId = null): void
    {
        try {
            Redis::setex(
                $this->cartRedisKey($userId),
                self::CART_TTL_SECONDS,
                json_encode(array_values($cart))
            );
        } catch (Throwable $e) {
            // Fallback: keep request successful even if Redis is down.
        }
    }

    private function clearCartRedis(?int $userId = null): void
    {
        try {
            Redis::del($this->cartRedisKey($userId));
        } catch (Throwable $e) {
            // Fallback: ignore Redis clear failures.
        }
    }

    private function getDatabaseCartItems(int $userId): array
    {
        $cart = $this->repo->firstOrCreateCartByUser($userId);

        return $this->repo->getCartItemsWithProduct($cart->id)->map(function ($item) {
            return [
                'id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'image' => $item->product->image,
            ];
        })->values()->toArray();
    }

    private function getSessionCartItems(): array
    {
        return array_values(session()->get('cart', []));
    }

    private function mapItemsToSessionCart(array $items): array
    {
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

        return $sessionCart;
    }

    private function queueCartSync(int $userId): void
    {
        try {
            if (!Redis::exists($this->cartRedisKey($userId))) {
                return;
            }

            SyncUserCartFromRedisJob::dispatch($userId)->afterResponse();
        } catch (Throwable $e) {
            // Ignore queue dispatch issues to keep UX instant.
        }
    }

    private function queueGuestCartSync(): void
    {
        try {
            if (!Redis::exists($this->cartRedisKey())) {
                return;
            }

            SyncGuestCartSessionFromRedisJob::dispatch(session()->getId())->afterResponse();
        } catch (Throwable $e) {
            // Ignore queue dispatch issues to keep UX instant.
        }
    }

    public function syncGuestCart(int $id): void
    {
        $guestItems = $this->getCartFromRedis() ?? $this->getSessionCartItems();

        if (!empty($guestItems)) {
            $guestCart = $this->mapItemsToSessionCart($guestItems);
            $this->repo->syncGuestCartItems($id, $guestCart);
            session()->forget('cart');

            $this->clearCartRedis();
            $this->saveCartToRedis($this->getDatabaseCartItems($id), $id);
        }
    }

    public function getCartItems(): array
    {
        if (Auth::check()) {
            $userId = (int) Auth::id();
            $cached = $this->getCartFromRedis($userId);
            if ($cached !== null) {
                return $cached;
            }

            $items = $this->getDatabaseCartItems($userId);
            $this->saveCartToRedis($items, $userId);
            return $items;
        }

        $cached = $this->getCartFromRedis();
        if ($cached !== null) {
            return $cached;
        }

        $items = $this->getSessionCartItems();
        $this->saveCartToRedis($items);
        return $items;
    }

    public function getCheckoutCartItems(bool $isBuyNow): array
    {
        session(['is_buy_now' => $isBuyNow]);

        if ($isBuyNow) {
            return session('buy_now_items', []);
        }

        return $this->getCartItems();
    }

    public function clearCheckoutCart(bool $isBuyNow): void
    {
        if (!$isBuyNow) {
            if (Auth::check()) {
                $userId = (int) Auth::id();
                $cart = $this->repo->getCartForUser($userId);
                if ($cart) {
                    $this->repo->clearCartItems($cart->id);
                }

                $this->clearCartRedis($userId);
            }
            session()->forget('cart');
            $this->clearCartRedis();
        }

        session()->forget(['checkout_address_id', 'checkout_notes', 'is_buy_now', 'buy_now_items']);
    }

    public function addToCart(string $productId, string $quantity): array
    {
        $productId = (int) $productId;
        $quantity = (int) $quantity;
        $product = $this->repo->findProduct($productId);

        if (!$product) {
            return [
                'error' => 'Product not found.',
                'totalQuantity' => 0,
                'cartCount' => 0,
            ];
        }

        if (Auth::check()) {
            // Redis-first storage for logged-in users. DB sync happens in queue.
            $userId = (int) Auth::id();
            $cart = $this->getCartItems();
            $found = false;

            foreach ($cart as &$item) {
                if ((int) ($item['id'] ?? 0) !== $productId) {
                    continue;
                }

                $item['quantity'] = (int) ($item['quantity'] ?? 0) + $quantity;
                $found = true;
                break;
            }
            unset($item);

            if (!$found) {
                $cart[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'image' => $product->image,
                ];
            }

            $this->saveCartToRedis($cart, $userId);
            $this->queueCartSync($userId);

            $totalQuantity = (int) collect($cart)->sum('quantity');
            $cartCount = count($cart);
        } else {
            // Redis-first storage for guests. Session sync happens in queue.
            $cart = $this->getCartItems();
            $found = false;

            foreach ($cart as &$item) {
                if ((int) ($item['id'] ?? 0) !== $productId) {
                    continue;
                }

                $item['quantity'] = (int) ($item['quantity'] ?? 0) + $quantity;
                $found = true;
                break;
            }
            unset($item);

            if (!$found) {
                $cart[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'image' => $product->image
                ];
            }

            $totalQuantity = collect($cart)->sum('quantity');
            $cartCount = count($cart);
            $this->saveCartToRedis(array_values($cart));
            $this->queueGuestCartSync();
        }

        return [
            'totalQuantity' => $totalQuantity,
            'cartCount' => $cartCount
        ];
    }

    public function update(int $itemId, int $quantity): array
    {
        $total = 0;
        $totalQuantity = 0;

        if (Auth::check()) {
            $userId = (int) Auth::id();
            $cartItems = $this->getCartItems();

            foreach ($cartItems as &$item) {
                if ((int) ($item['id'] ?? 0) !== $itemId) {
                    continue;
                }

                $item['quantity'] = max(1, $quantity);
                break;
            }
            unset($item);

            $this->saveCartToRedis($cartItems, $userId);
            $this->queueCartSync($userId);

            $total = $this->calculateTotal($cartItems);
            $totalQuantity = (int) collect($cartItems)->sum('quantity');
        } else {
            $cartItems = $this->getCartItems();

            foreach ($cartItems as &$item) {
                if ((int) ($item['id'] ?? 0) !== $itemId) {
                    continue;
                }

                $item['quantity'] = max(1, $quantity);
                break;
            }
            unset($item);

            $total = $this->calculateTotal($cartItems);
            $totalQuantity = collect($cartItems)->sum('quantity');
            $this->saveCartToRedis(array_values($cartItems));
            $this->queueGuestCartSync();
        }

        return [
            'totalQuantity' => $totalQuantity,
            'total' => $total
        ];

    }

    public function calculateTotal($items)
    {
        return array_reduce($items, function($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);
    }

    public function remove(int $itemId): array
    {
        $total = 0;
        $totalQuantity = 0;

        if (Auth::check()) {
            $userId = (int) Auth::id();
            $cartItems = array_values(array_filter($this->getCartItems(), function ($item) use ($itemId) {
                return (int) ($item['id'] ?? 0) !== $itemId;
            }));

            $this->saveCartToRedis($cartItems, $userId);
            $this->queueCartSync($userId);

            $total = $this->calculateTotal($cartItems);
            $totalQuantity = (int) collect($cartItems)->sum('quantity');
        } else {
            $cartItems = array_values(array_filter($this->getCartItems(), function ($item) use ($itemId) {
                return (int) ($item['id'] ?? 0) !== $itemId;
            }));

            $total = $this->calculateTotal($cartItems);
            $totalQuantity = collect($cartItems)->sum('quantity');
            $this->saveCartToRedis(array_values($cartItems));
            $this->queueGuestCartSync();
        }

        return [
            'totalQuantity' => $totalQuantity,
            'total' => $total
        ];
    }

}




