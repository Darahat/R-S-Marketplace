<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
 use Stripe\Stripe;
use Stripe\PaymentMethod as StripePaymentMethod;
use App\Models\UserPaymentMethod;
class DetachStripePaymentMethodJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public UserPaymentMethod $method)
    {
         $this->method =  $method;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        StripePaymentMethod::retrieve(($this->method)->detach());
    }
}



