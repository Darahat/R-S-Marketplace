<?php

namespace App\Services;

class UserSessionSyncService
{
    public function __construct(
        private CartService $cartService,
        private WishlistService $wishlistService,
    ) {
    }

    public function syncGuestDataToUser(int $userId): void
    {
        $this->cartService->syncGuestCart($userId);
        $this->wishlistService->syncGuestWishlist($userId);
    }
}
