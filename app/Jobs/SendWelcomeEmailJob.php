<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public User $user,
        public string $message)
    {}

    /**
     * Execute the job.
     */
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
