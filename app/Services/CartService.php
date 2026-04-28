<?php
namespace App\Services;

use App\Jobs\SyncGuestCartSessionFromRedisJob;
use App\Jobs\SyncUserCartFromRedisJob;
use App\Repositories\CartRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CartService
{
    private const CART_TTL_SECONDS = 2592000; // 30 days
    private const REDIS_PREFIX = 'cart:';
    private const USER_PREFIX = 'user:';
    private const GUEST_PREFIX = 'guest:';

    public function __construct(protected CartRepository $repo)
    {
    }

    private function cartRedisKey(?int $userId = null, ?string $guestId = null): string
    {
        if ($userId) {
            return self::REDIS_PREFIX . self::USER_PREFIX . $userId;
        }
        if ($guestId) {
            return self::REDIS_PREFIX . self::GUEST_PREFIX . $guestId;
        }

        if (!session()->isStarted()) {
            session()->start();
        }

        return self::REDIS_PREFIX . self::GUEST_PREFIX . session()->getId();
    }

    private function getGuestIdentifier(): string
    {
        if (!session()->has('guest_cart_id')) {
            session()->put('guest_cart_id', 'guest_' . Str::uuid()->toString());
        }
        return session()->get('guest_cart_id');
    }

    private function getCartFromRedis(?int $userId = null, ?string $guestId = null): ?array
    {
        try {
            $key = $this->cartRedisKey($userId, $guestId);
            $payload = Cache::store('redis')->get($key);

            if (!$payload) {
                return null;
            }

            $decoded = json_decode($payload, true);

            // Handle old numeric indexed format
            if (is_array($decoded) && isset($decoded[0]) && !isset($decoded['id'])) {
                $newFormat = [];
                foreach ($decoded as $item) {
                    if (isset($item['id'])) {
                        $newFormat[$item['id']] = $item;
                    }
                }
                return $newFormat;
            }

            return is_array($decoded) ? $decoded : null;
        } catch (Throwable $e) {
            Log::warning('Redis get failed', ['error' => $e->getMessage(), 'user_id' => $userId]);

            if ($userId) {
                return $this->getDatabaseCartItems($userId);
            } elseif ($guestId) {
                return $this->getSessionCartItems();
            }
            return null;
        }
    }

    private function saveCartToRedis(array $cart, ?int $userId = null, ?string $guestId = null): bool
    {
        // Use product ID as key for O(1) lookups
        $cartWithKeys = [];
        foreach ($cart as $item) {
            if (isset($item['id'])) {
                $cartWithKeys[$item['id']] = $item;
            }
        }

        try {
            $key = $this->cartRedisKey($userId, $guestId);
            $success = Cache::store('redis')->put($key, json_encode($cartWithKeys), self::CART_TTL_SECONDS);

            if (!$success) {
                throw new \Exception('Redis put operation failed');
            }
            return true;
        } catch (Throwable $e) {
            Log::error('Redis save failed, using fallback', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'guest_id' => $guestId,
            ]);

            // Fallback to session for guests
            if (!$userId && $guestId) {
                $this->saveCartToSession($cartWithKeys);
            }

            // For logged-in users, save directly to database using existing method
            if ($userId) {
                $this->saveCartToDatabaseSync($userId, $cartWithKeys);
            }
            return false;
        }
    }

    private function saveCartToSession(array $cart): void
    {
        $sessionCart = [];
        foreach ($cart as $item) {
            $sessionCart[$item['id']] = $item;
        }
        session()->put('cart', $sessionCart);
    }

    private function saveCartToDatabaseSync(int $userId, array $cart): void
    {
        try {
            // Use existing syncGuestCartItems method instead of non-existent syncCartItems
            $this->repo->syncGuestCartItems($userId, $cart);
        } catch (Throwable $e) {
            Log::critical('Failed to save cart to database as fallback', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function clearCartRedis(?int $userId = null, ?string $guestId = null): void
    {
        try {
            $key = $this->cartRedisKey($userId, $guestId);
            Cache::store('redis')->forget($key);
        } catch (Throwable $e) {
            Log::warning('Redis clear failed', ['error' => $e->getMessage()]);
        }
    }

    private function getDatabaseCartItems(int $userId): array
    {
        $cart = $this->repo->firstOrCreateCartByUser($userId);

        $items = $this->repo->getCartItemsWithProduct($cart->id)->map(function ($item) {
            return [
                'id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'image' => $item->product->image,
            ];
        })->toArray();

        // Return with product ID as key for consistency
        $itemsWithKeys = [];
        foreach ($items as $item) {
            $itemsWithKeys[$item['id']] = $item;
        }
        return $itemsWithKeys;
    }

    private function getSessionCartItems(): array
    {
        $sessionCart = session()->get('cart', []);

        // Handle old numeric indexed format
        if (is_array($sessionCart) && isset($sessionCart[0]) && !isset($sessionCart['id'])) {
            $newFormat = [];
            foreach ($sessionCart as $item) {
                if (isset($item['id'])) {
                    $newFormat[$item['id']] = $item;
                }
            }
            return $newFormat;
        }

        // Ensure all items have required fields
        foreach ($sessionCart as $id => &$item) {
            if (!isset($item['id'])) {
                $item['id'] = $id;
            }
            $item['quantity'] = max(1, $item['quantity'] ?? 1);
        }

        return $sessionCart;
    }

    private function queueCartSync(int $userId): void
    {
        try {
            $key = $this->cartRedisKey($userId);
            if (!Cache::store('redis')->has($key)) {
                return;
            }

            SyncUserCartFromRedisJob::dispatch($userId)->onQueue('cart-sync')->delay(now()->addSeconds(2));
        } catch (Throwable $e) {
            Log::error('Failed to dispatch cart sync job', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function queueGuestCartSync(string $guestId): void
    {
        try {
            $key = $this->cartRedisKey(null, $guestId); // Fixed: added null for userId
            if (!Cache::store('redis')->has($key)) {
                return;
            }

            SyncGuestCartSessionFromRedisJob::dispatch($guestId)->onQueue('cart-sync')->delay(now()->addSeconds(2)); // Fixed: correct job class
        } catch (Throwable $e) {
            Log::error('Failed to dispatch guest cart sync job', [
                'guest_id' => $guestId,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function syncGuestCart(int $userId): void
    {
        $guestId = $this->getGuestIdentifier();
        $guestItems = $this->getCartFromRedis(null, $guestId) ?? $this->getSessionCartItems();

        if (!empty($guestItems)) {
            // Merge guest cart with existing user cart
            $userItems = $this->getDatabaseCartItems($userId);
            foreach ($guestItems as $productId => $item) {
                if (isset($userItems[$productId])) {
                    $userItems[$productId]['quantity'] += $item['quantity'];
                } else {
                    $userItems[$productId] = $item;
                }
            }

            $this->repo->syncGuestCartItems($userId, $userItems);
            session()->forget('cart');
            session()->forget('guest_cart_id');
            $this->clearCartRedis(null, $guestId);
            $this->saveCartToRedis($userItems, $userId);
        }
    }

    public function getCartItems(): array
    {
        if (Auth::check()) {
            $userId = Auth::id();
            $cached = $this->getCartFromRedis($userId);
            if ($cached !== null) {
                return $cached;
            }

            $items = $this->getDatabaseCartItems($userId);
            $this->saveCartToRedis($items, $userId);
            return $items;
        }

        $guestId = $this->getGuestIdentifier();
        $cached = $this->getCartFromRedis(null, $guestId);

        if ($cached !== null) {
            return $cached;
        }

        $items = $this->getSessionCartItems();
        $this->saveCartToRedis($items, null, $guestId);
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
                $userId = Auth::id();
                $cart = $this->repo->getCartForUser($userId);
                if ($cart) {
                    $this->repo->clearCartItems($cart->id);
                }
                $this->clearCartRedis($userId);
            }
            session()->forget('cart');
            $guestId = $this->getGuestIdentifier();
            $this->clearCartRedis(null, $guestId);
        }

        session()->forget(['checkout_address_id', 'checkout_notes', 'is_buy_now', 'buy_now_items']);
    }

    public function addToCart(int $productId, int $quantity): array
    {
        $product = $this->repo->findProduct($productId);

        if (!$product) {
            return [
                'error' => 'Product not found.',
                'totalQuantity' => 0,
                'cartCount' => 0,
            ];
        }

        $quantity = max(1, $quantity);

        if (Auth::check()) {
            $userId = Auth::id();
            $maxRetries = 3;
            $cart = [];

            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                $cart = $this->getCartItems();

                if (isset($cart[$productId])) {
                    $cart[$productId]['quantity'] += $quantity;
                } else {
                    $cart[$productId] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'quantity' => $quantity,
                        'image' => $product->image,
                    ];
                }

                if ($this->saveCartToRedis($cart, $userId)) {
                    break;
                }

                if ($attempt === $maxRetries) {
                    Log::error('Failed to save cart after max retries', ['user_id' => $userId]);
                }
                usleep(100000);
            }

            $this->queueCartSync($userId);
            $totalQuantity = collect($cart)->sum('quantity');
            $cartCount = count($cart);
        } else {
            $guestId = $this->getGuestIdentifier();
            $cart = $this->getCartItems();

            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] += $quantity;
            } else {
                $cart[$productId] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'image' => $product->image,
                ];
            }

            $this->saveCartToRedis($cart, null, $guestId);
            $this->queueGuestCartSync($guestId);
            $totalQuantity = collect($cart)->sum('quantity');
            $cartCount = count($cart);
        }

        return [
            'totalQuantity' => $totalQuantity,
            'cartCount' => $cartCount
        ];
    }

    public function update(int $productId, int $quantity): array
    {
        $quantity = max(1, $quantity);

        if (Auth::check()) {
            $userId = Auth::id();
            $cart = $this->getCartItems(); // Fixed: use $cart consistently

            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] = $quantity;
                $this->saveCartToRedis($cart, $userId);
                $this->queueCartSync($userId);
            }

            $total = $this->calculateTotal($cart);
            $totalQuantity = collect($cart)->sum('quantity');
        } else {
            $guestId = $this->getGuestIdentifier();
            $cart = $this->getCartItems(); // Fixed: use $cart consistently

            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] = $quantity;
                $this->saveCartToRedis($cart, null, $guestId);
                $this->queueGuestCartSync($guestId);
            }

            $total = $this->calculateTotal($cart);
            $totalQuantity = collect($cart)->sum('quantity');
        }

        return [
            'totalQuantity' => $totalQuantity,
            'total' => $total
        ];
    }

    public function calculateTotal($items)
    {
        return array_reduce($items, function ($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);
    }

    public function remove(int $productId): array  // Fixed: renamed parameter to match usage
    {
        if (Auth::check()) {
            $userId = Auth::id();
            $cart = $this->getCartItems(); // Fixed: use $cart consistently

            if (isset($cart[$productId])) {
                unset($cart[$productId]);
                $this->saveCartToRedis($cart, $userId);
                $this->queueCartSync($userId);
            }

            $total = $this->calculateTotal($cart);
            $totalQuantity = collect($cart)->sum('quantity');
        } else {
            $guestId = $this->getGuestIdentifier();
            $cart = $this->getCartItems(); // Fixed: use $cart consistently

            if (isset($cart[$productId])) {
                unset($cart[$productId]);
                $this->saveCartToRedis($cart, null, $guestId);
                $this->queueGuestCartSync($guestId);
            }

            $total = $this->calculateTotal($cart);
            $totalQuantity = collect($cart)->sum('quantity');
        }

        return [
            'totalQuantity' => $totalQuantity,
            'total' => $total
        ];
    }
}
