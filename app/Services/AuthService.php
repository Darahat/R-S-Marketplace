<?php

namespace App\Services;
use Illuminate\Support\Facades\Mail;

use App\Models\User;
use App\Services\WishlistService;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Mail\WelcomeMail;

use App\Jobs\SendWelcomeEmailJob;
class AuthService{
  public function __construct(protected WishlistService $wishlistService, protected CartService $cartService)
    {

    }
    public function attemptLogin(array $userCredential, bool $isRemember = false, string $ip, string $userAgent): ?User{

        if(!Auth::attempt($userCredential, $isRemember)){
            return null;
        };
        session()->regenerate();
        $user = Auth::user();
        if ($user) {
                $this->recordLoginMetaData($user,$ip,$userAgent);
                $this->syncUserData($user);
            }
        return $user;

    }
   public function recordLoginMetaData(User $user, String $ip,String $userAgent):void{

    $device = $this->parseDeviceName($userAgent);

    $success = $user->update([
        'last_login' => now(),
        'last_ip' => $ip,
        'last_device' => $device
    ]);
    Log::info('User login metadata updated', [
        'user_id' => $user->id,
        'updated' => $success,
        'last_device' => $device,
    ]);
   }
   public function redirectByRole(User $user){
    return match($user->user_type){
        'ADMIN' => route('admin.dashboard'),
        'CUSTOMER' => route('home'),
        default => route('home'),
   };
   }
    private function syncUserData(User $user): void
    {
          // Sync guest cart to user cart
                $this->cartService->syncGuestCart($user->id);

                // Sync guest wishlist to user wishlist
                $this->wishlistService->syncGuestWishlist($user->id);
    }
     public function logout()
    {
        Auth::logout();


    }

    private function parseDeviceName(string $userAgent): string
    {
        $agent = trim($userAgent);

        if ($agent === '') {
            return 'Unknown Device';
        }

        $browser = 'Unknown Browser';
        if (str_contains($agent, 'Edg/')) {
            $browser = 'Microsoft Edge';
        } elseif (str_contains($agent, 'OPR/') || str_contains($agent, 'Opera')) {
            $browser = 'Opera';
        } elseif (str_contains($agent, 'Chrome/')) {
            $browser = 'Google Chrome';
        } elseif (str_contains($agent, 'Firefox/')) {
            $browser = 'Mozilla Firefox';
        } elseif (str_contains($agent, 'Safari/') && !str_contains($agent, 'Chrome/')) {
            $browser = 'Safari';
        } elseif (str_contains($agent, 'MSIE') || str_contains($agent, 'Trident/')) {
            $browser = 'Internet Explorer';
        }

        $platform = 'Unknown OS';
        if (str_contains($agent, 'Windows NT 10.0')) {
            $platform = 'Windows 10/11';
        } elseif (str_contains($agent, 'Windows NT 6.3')) {
            $platform = 'Windows 8.1';
        } elseif (str_contains($agent, 'Windows NT 6.2')) {
            $platform = 'Windows 8';
        } elseif (str_contains($agent, 'Windows NT 6.1')) {
            $platform = 'Windows 7';
        } elseif (str_contains($agent, 'Android')) {
            $platform = 'Android';
        } elseif (str_contains($agent, 'iPhone') || str_contains($agent, 'iPad')) {
            $platform = 'iOS';
        } elseif (str_contains($agent, 'Mac OS X')) {
            $platform = 'macOS';
        } elseif (str_contains($agent, 'Linux')) {
            $platform = 'Linux';
        }

        return $browser . ' on ' . $platform;
    }

    public function register(array $data,string $ip,string $device):User{
          $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'mobile' => $data['mobile'],
            'user_type' => 'CUSTOMER',
        ]);
        Auth::login($user);
        $user = Auth::user();
        if ($user) {
                SendWelcomeEmailJob::dispatch($user, 'Welcome aboard!');
                    // Mail::to('admin@example.com')->send(new WelcomeMail("hello i am mail test"));

                $this->recordLoginMetaData($user,$ip,$device);
                $this->syncUserData($user);

            }
        return $user;
    }
}
