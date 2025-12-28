<?php

namespace App\Http\Middleware;

use Closure;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use PrometheusPushGateway\PushGateway;
use Prometheus\RenderTextFormat;

class CollectMetrics
    {
        public function handle($request, Closure $next)
        {
            $start = microtime(true);
            $response = $next($request);
            $duration = microtime(true) - $start;

            // Use an in-memory registry and push to PushGateway (persistent)
            $registry = new CollectorRegistry(new InMemory());

            // Count Requests
            $counter = $registry->getOrRegisterCounter(
                'laravel',
                'http_requests_total',
                'Total HTTP requests',
                ['method', 'endpoint', 'status'],
            );
            $counter->inc([
                $request->method(),
                $request->path(),
                $response->getStatusCode()
            ]);
            // Measure response time
            $histogram = $registry->getOrRegisterHistogram(
                'laravel',
                'http_response_time_seconds',
                'HTTP response time',
                ['method', 'endpoint'],
                [0.1, 0.5, 1.0, 2.0, 5.0]
            );
            $histogram->observe($duration,[
                $request->method(),
                $request->path()
            ]);

            // Push metrics to PushGateway so Prometheus can scrape them
            // Access via host port mapping from Windows: http://localhost:9091
            // Use pushAdd to accumulate counters across requests
            try {
                $pushGateway = new PushGateway('http://localhost:9091');
                $pushGateway->pushAdd($registry, 'laravel', [
                    'instance' => 'local',
                    'application' => 'R&S Marketplace'
                ]);
            } catch (\Throwable $e) {
                // Silently ignore push failures to not break requests
            }
            return $response;
        }
    }