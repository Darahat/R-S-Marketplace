<?php

namespace App\Services;

use App\Models\User;
use App\Services\CartAndWishlistService;
use Illuminate\Support\Facades\Auth;
class AuthService{
  public function __construct(protected CartAndWishlistService $cartAndWishlistService)
    {

    }
   public function recordLoginMetaData(User $user, String $ip,String $device):void{
    $user->update([
        'last_login' => now(),
        'last_ip' => $ip,
        'last_device' => $device
    ]);
   }
   public function redirectByRole(User $user){
    return match($user->user_type){
        'ADMIN' => redirect()->intended(route('admin.dashboard')),
        'CUSTOMER' => redirect()->intended(route('home')),
        default => redirect()->intended(route('home')),
   };
   }
    public function syncUserData(User $user): void
    {
          // Sync guest cart to user cart
                $this->cartAndWishlistService->syncGuestCart($user->id);

                // Sync guest wishlist to user wishlist
                $this->cartAndWishlistService->syncGuestWishlist($user->id);
    }
     public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

    }
}
