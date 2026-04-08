<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
 use Stripe\Stripe;
use Stripe\PaymentMethod as StripePaymentMethod;
use App\Models\UserPaymentMethod;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class DetachStripePaymentMethodJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public UserPaymentMethod $method)
    {
         $this->method =  $method;
    }
    public int $tries = 3;
    public array $backoff = [30,60,120]; // wait 30s, the 60s, then 120s between retries
    public int $uniqueFor = 300; // 5-minute lock
    /**
     * Execute the job.
     */
    public function uniqueId():string
    {
        return 'detach_stripe_pm_'. $this->method->id;
    }
    public function handle(): void
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        StripePaymentMethod::retrieve(($this->method)->detach());
    }
}



