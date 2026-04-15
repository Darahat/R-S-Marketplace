<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Repositories\BrandRepository;
use Illuminate\Support\Facades\Mail;
use App\Mail\BrandCreatedNotification;
use Illuminate\Support\Facades\Log;

class BrandCreatedNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $brandId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(BrandRepository $brandRepository): void
    {
        $brand = $brandRepository->findBrand($this->brandId);

        if (!$brand) {
            Log::warning('BrandCreatedNotificationJob: brand not found', [
                'brand_id' => $this->brandId,
            ]);
            return;
        }

        Mail::to('admin@example.com')->send(new BrandCreatedNotification($brand));
    }
}
