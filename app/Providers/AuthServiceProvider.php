<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies =[
        \App\Models\UserPaymentMethod::class => \App\Policies\PaymentMethodPolicy::class,
        \App\Models\Address::class => \App\Policies\UserAddressPolicy::class,
    ];
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
