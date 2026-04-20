<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\AuthRepository;
use App\Repositories\LoginAuditRepository;
use Illuminate\Support\Facades\Auth;

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
}
