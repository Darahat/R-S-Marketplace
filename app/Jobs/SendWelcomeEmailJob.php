<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendWelcomeEmailJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public User $user,
        public string $message)
    {}

    public int $tries= 3;
    public array $backoff = [60,120,300];
    public int $uniqueFor = 600; // 10-minute lock
    /**
     * Execute the job.
     */
    public function uniqueId(): string
    {
        return 'welcome_email_user_' . $this->user->id;
    }
    public function handle(): void
    {
        Mail::to($this->user->email)->send(new WelcomeMail($this->message));
    }

    // Called when All retries are exhausted
    // Job moves to failed jobs table
    public function failed(\Throwable $e): void{
                Log::error('Email job failed: ' . $e->getMessage());

    }
}
