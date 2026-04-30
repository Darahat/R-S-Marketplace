<?php
namespace App\Services;
use App\Jobs\SyncGuestWishlistSessionFromRedisJob;
use App\Jobs\SyncUserWishlistFromRedisJob;
use App\Repositories\WishlistRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\Services\CartService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class WishlistService{
    private const WISHLIST_TTL_SECONDS = 2592000; // 30 days

    public function __construct(
            protected CartService $cartService,
            protected WishlistRepository $repo,
    )
    {

    }

    private function getGuestIdentifier(): string
    {
        if (!session()->has('guest_cart_id')) {
            session()->put('guest_cart_id', 'guest_' . \Illuminate\Support\Str::uuid()->toString());
        }
        return session()->get('guest_cart_id');
    }

    private function wishlistRedisKey(?int $userId = null, ?string $guestId = null): string
    {
        if ($userId) {
            return "wishlist:user:{$userId}";
        }

        $id = $guestId ?? $this->getGuestIdentifier();
        return "wishlist:guest:{$id}";
    }

    private function getWishlistFromRedis(?int $userId = null, ?string $guestId = null): ?array
    {
        try {
            $key = $this->wishlistRedisKey($userId, $guestId);
            $payload = Cache::store('redis')->get($key);

            if (!$payload) {
                 return null;
            }

            $decoded = json_decode($payload, true);
            return is_array($decoded) ? $decoded : null;
        } catch (Throwable $e) {
        Log::warning('Redis get failed', ['error' => $e->getMessage(), 'user_id' => $userId]);
 if ($userId) {
                return $this->getDatabaseWishlistItems($userId);
            } elseif ($guestId) {
                return $this->getSessionWishlistItems();
            }
            return null;
        }
    }

    private function saveWishlistToRedis(array $items, ?int $userId = null, ?string $guestId = null): void
    {
        try {
            $key = $this->wishlistRedisKey($userId, $guestId);
            Cache::store('redis')->put(
                $key,
                json_encode(array_values($items)),
                self::WISHLIST_TTL_SECONDS
            );
        } catch (Throwable $e) {
  Log::error('Redis save failed, using fallback', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'guest_id' => $guestId,
            ]);        }
    }

    private function clearWishlistRedis(?int $userId = null, ?string $guestId = null): void
    {
        try {
            $key = $this->wishlistRedisKey($userId, $guestId);
            Cache::store('redis')->forget($key);
        } catch (Throwable $e) {
            // Fallback: ignore Redis clear failures.
        }
    }

    private function getDatabaseWishlistItems(int $userId): array
    {
        $wishlist = $this->repo->firstOrCreateForUser($userId);

        return $this->repo->getItemsWithProduct($wishlist)->map(function ($item) {
            return [
                'id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $item->product->price,
                'image' => $item->product->image,
                'slug' => $item->product->slug ?? '',
                'stock' => $item->product->stock ?? 0,
            ];
        })->values()->toArray();
    }

    private function getSessionWishlistItems(): array
    {
        $sessionWishlist = session('wishlist', []);
        $wishlistItems = [];

        foreach ($sessionWishlist as $productId) {
            $product = $this->repo->findProduct((int) $productId);
            if (!$product) {
                continue;
            }

            $wishlistItems[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->image,
                'slug' => $product->slug ?? '',
                'stock' => $product->stock ?? 0,
            ];
        }

        return $wishlistItems;
    }

    private function queueWishlistSync(int $userId): void
    {
        try {
            if (!Redis::exists($this->wishlistRedisKey($userId))) {
                return;
            }

            SyncUserWishlistFromRedisJob::dispatch($userId)->afterResponse();
        } catch (Throwable $e) {
            // Ignore queue dispatch issues to keep UX instant.
        }
    }

    private function queueGuestWishlistSync(): void
    {
        try {
            if (!Redis::exists($this->wishlistRedisKey())) {
                return;
            }

            SyncGuestWishlistSessionFromRedisJob::dispatch(session()->getId())->afterResponse();
        } catch (Throwable $e) {
            // Ignore queue dispatch issues to keep UX instant.
        }
    }

    /**
     * Sync guest wishlist to database when user logs in
     */

      public function syncGuestWishlist($id):void
    {
        $guestItems = $this->getWishlistFromRedis() ?? $this->getSessionWishlistItems();

        if (!empty($guestItems)) {
            $guestWishlist = array_values(array_unique(array_map(function ($item) {
                return (int) ($item['id'] ?? 0);
            }, $guestItems)));

            $wishlist = $this->repo->firstOrCreateForUser($id);

            foreach ($guestWishlist as $productId) {
                if ($productId <= 0) {
                    continue;
                }
                $this->repo->firstOrCreateItem($wishlist->id, (int) $productId);
            }

            session()->forget('wishlist');
            $this->clearWishlistRedis();
            $this->saveWishlistToRedis($this->getDatabaseWishlistItems((int) $id), (int) $id);
        }
    }
    public function getWishlistItems():Array
    {
        if (Auth::check()) {
            $userId = (int) Auth::id();
            $cached = $this->getWishlistFromRedis($userId);
            if ($cached !== null) {
                return $cached;
            }

            $items = $this->getDatabaseWishlistItems($userId);
            $this->saveWishlistToRedis($items, $userId);
            return $items;
        }

        $guestId = $this->getGuestIdentifier();
        $cached = $this->getWishlistFromRedis(null, $guestId);
        if ($cached !== null) {
            return $cached;
        }

        $wishlistItems = $this->getSessionWishlistItems();
        $this->saveWishlistToRedis($wishlistItems, null, $guestId);
        return $wishlistItems;
    }
    /**
     * Get wishlist count
     */
    public function getWishlistCount():Int
    {
        return count($this->getWishlistItems());
    }
        /**
     * Move wishlist item to cart
     */
    public function wishlistMoveToCart(int $productId):Array
    {

        // Remove from wishlist
        if (Auth::check()) {
            $userId = (int) Auth::id();
            $items = array_values(array_filter($this->getWishlistItems(), function ($item) use ($productId) {
                return (int) ($item['id'] ?? 0) !== $productId;
            }));

            $this->saveWishlistToRedis($items, $userId);
            // $this->queueWishlistSync($userId);
            $wishlistCount = count($items);
        } else {
            $guestId = $this->getGuestIdentifier();
            $items = array_values(array_filter($this->getWishlistItems(), function ($item) use ($productId) {
                return (int) ($item['id'] ?? 0) !== $productId;
            }));

            $wishlistCount = count($items);
            $this->saveWishlistToRedis($items, null, $guestId);
            // $this->queueGuestWishlistSync();
        }

        // Add to cart
        $cartData = $this->cartService->addToCart((string) $productId, '1');
        $totalQuantity = $cartData['totalQuantity'];
        $cartCount  = $cartData['cartCount'];
                   return [
    'wishlistCount' =>$wishlistCount,
    'totalQuantity' => $totalQuantity,
    'quantity' => $cartCount,
    'cart' => $cartData
];
    }

    public function removeWishlistProduct(int $productId):int{
        $wishlistCount = 0;
         if (Auth::check()) {
            $userId = (int) Auth::id();
            $items = array_values(array_filter($this->getWishlistItems(), function ($item) use ($productId) {
                return (int) ($item['id'] ?? 0) !== $productId;
            }));

            $this->saveWishlistToRedis($items, $userId);
            // $this->queueWishlistSync($userId);
            $wishlistCount = count($items);
        } else {
            $guestId = $this->getGuestIdentifier();
            $items = array_values(array_filter($this->getWishlistItems(), function ($item) use ($productId) {
                return (int) ($item['id'] ?? 0) !== $productId;
            }));

            $wishlistCount = count($items);
            $this->saveWishlistToRedis($items, null, $guestId);
            // $this->queueGuestWishlistSync();

        }
                    return $wishlistCount;
    }

    public function wishlistToggle(int $productId):array{


        if (Auth::check()) {
            $userId = (int) Auth::id();
            $items = $this->getWishlistItems();
            $exists = false;

            foreach ($items as $item) {
                if ((int) ($item['id'] ?? 0) === $productId) {
                    $exists = true;
                    break;
                }
            }

            if ($exists) {
                $items = array_values(array_filter($items, function ($item) use ($productId) {
                    return (int) ($item['id'] ?? 0) !== $productId;
                }));
                $is_wishlisted = false;
            } else {
                $product = $this->repo->findProduct($productId);
                if ($product) {
                    $items[] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'image' => $product->image,
                        'slug' => $product->slug ?? '',
                        'stock' => $product->stock ?? 0,
                    ];
                }
                $is_wishlisted = true;
            }

            $this->saveWishlistToRedis($items, $userId);
            // $this->queueWishlistSync($userId);
            $count = count($items);
        } else {
            $guestId = $this->getGuestIdentifier();
            $items = $this->getWishlistItems();
            $exists = false;

            foreach ($items as $item) {
                if ((int) ($item['id'] ?? 0) === $productId) {
                    $exists = true;
                    break;
                }
            }

            if ($exists) {
                $items = array_values(array_filter($items, function ($item) use ($productId) {
                    return (int) ($item['id'] ?? 0) !== $productId;
                }));
                $is_wishlisted = false;
            } else {
                $product = $this->repo->findProduct($productId);
                if ($product) {
                    $items[] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'image' => $product->image,
                        'slug' => $product->slug ?? '',
                        'stock' => $product->stock ?? 0,
                    ];
                }
                $is_wishlisted = true;
            }

            $count = count($items);
            $this->saveWishlistToRedis($items, null, $guestId);
            // $this->queueGuestWishlistSync();
        }

        return [
            'is_wishlisted' =>$is_wishlisted,
            'count' => $count,
        ];
    }
}
