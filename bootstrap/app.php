<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except:[
            'stripe/webhook',
        ]);

        // Apply metrics collection to all requests
        $middleware->append(\App\Http\Middleware\CollectMetrics::class);

        $middleware->alias([
            'isAdmin' => \App\Http\Middleware\IsAdmin::class,
            'collectMetrics' => \App\Http\Middleware\CollectMetrics::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
