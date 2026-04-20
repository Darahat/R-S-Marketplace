<?php

namespace App\Jobs;

use App\Repositories\AuthRepository;
use App\Services\DeviceDetectionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RecordLoginMetaDataJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $userId,
        public string $ip,
        public string $userAgent,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(AuthRepository $authRepository, DeviceDetectionService $deviceDetectionService): void
    {
        $user = $authRepository->findUserById($this->userId);
        if (!$user) {
            Log::warning('RecordLoginMetaDataJob: user not found', ['user_id' => $this->userId]);
            return;
        }

        $device = $deviceDetectionService->parseDeviceName($this->userAgent);

        $updated = $authRepository->updateLoginMetaData($user, $this->ip, $device);

        Log::info('User login metadata updated (queued job)', [
            'user_id' => $user->id,
            'updated' => $updated,
            'last_device' => $device,
        ]);
    }
}
