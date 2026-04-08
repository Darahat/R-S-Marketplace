<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Brand;
use Illuminate\Support\Facades\Mail;
use App\Mail\BrandCreatedNotification;

class BrandCreatedNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Brand $method)
    {
        $this->method = $method;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to('admin@example.com')->send(new BrandCreatedNotification($this->method));
    }
}
