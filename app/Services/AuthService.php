<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\AuthRepository;
use App\Repositories\LoginAuditRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

use App\Jobs\SendWelcomeEmailJob;
use App\Jobs\RecordLoginMetaDataJob;
class AuthService{
    public function __construct(
                protected UserSessionSyncService $userSessionSyncService,
                protected DeviceDetectionService $deviceDetectionService,
                protected AuthRepository $authRepository,
                protected LoginAuditRepository $loginAuditRepository,
        )
    {

    }
    public function attemptLogin(array $userCredential, bool $isRemember = false, string $ip, string $userAgent): ?User{
        $email = $userCredential['email'] ?? null;
        $device = $this->deviceDetectionService->parseDeviceName($userAgent);

        if(!Auth::attempt($userCredential, $isRemember)){
            $this->loginAuditRepository->createAttempt([
                'user_id' => null,
                'email' => $email,
                'login_type' => 'customer',
                'status' => 'failed',
                'reason' => 'invalid_credentials',
                'ip' => $ip,
                'user_agent' => $userAgent,
                'device' => $device,
                'attempted_at' => now(),
            ]);
            return null;
        };
        session()->regenerate();
        $user = Auth::user();
        if ($user) {
                $this->loginAuditRepository->createAttempt([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'login_type' => 'customer',
                    'status' => 'success',
                    'reason' => null,
                    'ip' => $ip,
                    'user_agent' => $userAgent,
                    'device' => $device,
                    'attempted_at' => now(),
                ]);
                RecordLoginMetaDataJob::dispatch($user->id, $ip, $userAgent)->onQueue('default');
                $this->userSessionSyncService->syncGuestDataToUser($user->id);
            }
        return $user;

    }

    public function attemptAdminLogin(array $userCredential, bool $isRemember = false, string $ip, string $userAgent): ?User{
        $email = $userCredential['email'] ?? null;
        $device = $this->deviceDetectionService->parseDeviceName($userAgent);

        if(!Auth::attempt($userCredential, $isRemember)){
            $this->loginAuditRepository->createAttempt([
                'user_id' => null,
                'email' => $email,
                'login_type' => 'admin',
                'status' => 'failed',
                'reason' => 'invalid_credentials',
                'ip' => $ip,
                'user_agent' => $userAgent,
                'device' => $device,
                'attempted_at' => now(),
            ]);
            return null;
        };
        session()->regenerate();
        /** @var User|null $user */
        $user = Auth::user();

        // Check if user is admin
        if (!$user || !$user->isAdmin()) {
            $this->loginAuditRepository->createAttempt([
                'user_id' => $user?->id,
                'email' => $user?->email ?? $email,
                'login_type' => 'admin',
                'status' => 'failed',
                'reason' => 'not_admin',
                'ip' => $ip,
                'user_agent' => $userAgent,
                'device' => $device,
                'attempted_at' => now(),
            ]);
            Auth::logout();
            return null;
        }

        // Dispatch async job for admin metadata recording
        if ($user) {
            $this->loginAuditRepository->createAttempt([
                'user_id' => $user->id,
                'email' => $user->email,
                'login_type' => 'admin',
                'status' => 'success',
                'reason' => null,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'device' => $device,
                'attempted_at' => now(),
            ]);
            RecordLoginMetaDataJob::dispatch($user->id, $ip, $userAgent)->onQueue('default');
        }

        return $user;

    }
   public function redirectByRole(User $user){
    return match($user->user_type){
        'ADMIN' => route('admin.dashboard'),
        'CUSTOMER' => route('home'),
        default => route('home'),
   };
   }
     public function logout()
    {
        Auth::logout();


    }
    public function register(array $data,string $ip,string $device):User{
            $user = $this->authRepository->createCustomer($data);
        Auth::login($user);
        $user = Auth::user();
        if ($user) {
            SendWelcomeEmailJob::dispatch($user->id, 'Welcome aboard!')->delay(now()->addMinutes(2))->onQueue('emails');
                    // Mail::to('admin@example.com')->send(new WelcomeMail("hello i am mail test"));

                RecordLoginMetaDataJob::dispatch($user->id, $ip, $device)->onQueue('default');
                $this->userSessionSyncService->syncGuestDataToUser($user->id);

            }
        return $user;
    }

    public function getLoginAudits(array $filters, int $perPage = 20)
    {
        return $this->loginAuditRepository->getPaginated($filters, $perPage);
    }

    public function sendCustomerPasswordResetLink(string $email): string
    {
        $customer = $this->authRepository->findCustomerByEmail($email);

        // Avoid account enumeration and keep response consistent.
        if (!$customer) {
            return Password::RESET_LINK_SENT;
        }

        return Password::sendResetLink(['email' => $email]);
    }

    public function resetCustomerPassword(array $credentials): string
    {
        $customer = $this->authRepository->findCustomerByEmail($credentials['email']);
        if (!$customer) {
            return Password::INVALID_USER;
        }

        return Password::reset(
            $credentials,
            function ($user, $password) {
                $this->authRepository->updatePassword($user, $password, Str::random(60));
                event(new PasswordReset($user));
            }
        );
    }
}
