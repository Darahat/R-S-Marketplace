<?php

namespace App\Services;

use App\Jobs\RecordLoginMetaDataJob;
use App\Models\User;
use App\Notifications\AdminLoginAlertNotification;
use App\Repositories\LoginAuditRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminAuthService
{
    public function __construct(
        private DeviceDetectionService $deviceDetectionService,
        private LoginAuditRepository $loginAuditRepository,
    ) {
    }

    public function attemptLogin(array $userCredential, bool $isRemember = false, string $ip, string $userAgent): ?User
    {
        $email = $userCredential['email'] ?? null;
        $device = $this->deviceDetectionService->parseDeviceName($userAgent);

        if (!Auth::attempt($userCredential, $isRemember)) {
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
        }

        session()->regenerate();
        /** @var User|null $user */
        $user = Auth::user();

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
        $this->notifyAdminOnLogin($user, $ip, $device);

        return $user;
    }

    public function getLoginAudits(array $filters, int $perPage = 20)
    {
        return $this->loginAuditRepository->getPaginated($filters, $perPage);
    }

    private function notifyAdminOnLogin(User $user, string $ip, string $device): void
    {
        try {
            $user->notify(new AdminLoginAlertNotification(
                $ip,
                $device,
                now()->toDateTimeString(),
            ));
        } catch (\Throwable $exception) {
            Log::warning('Failed to send admin login alert notification.', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
