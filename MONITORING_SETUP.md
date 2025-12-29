# Grafana + Prometheus + PushGateway Monitoring Setup Guide

## Overview

This guide documents how to set up **Prometheus** and **Grafana** to monitor a Laravel application running on `localhost:8000` using **PushGateway** as a metric collector. This setup allows you to track HTTP requests, response times, and status codes in real-time.

---

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Your Browser (Windows Host)              │
│  http://localhost:3000 (Grafana)                            │
│  http://localhost:9090 (Prometheus)                         │
│  http://localhost:9091 (PushGateway)                        │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                    Docker Network                           │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Prometheus   │  │   Grafana    │  │ PushGateway  │      │
│  │  :9090       │  │   :3000      │  │   :9091      │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│         ↑                  ↓                    ↑            │
│         │           Query Prometheus           │            │
│         └───────────────────────────────────────┘            │
│                                                              │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│              Laravel App (Host: Windows)                    │
│  http://127.0.0.1:8000                                      │
│  CollectMetrics Middleware → Pushes to PushGateway:9091     │
└─────────────────────────────────────────────────────────────┘
```

---

## Problems Faced & Solutions

### Problem 1: "Class Redis not found"

**Cause**: The PHP Redis extension (`\Redis`) wasn't installed on Windows. Predis (PHP library) couldn't replace it.

**Solution**: Switched from Redis storage to **PushGateway**. Metrics are collected in-memory per request, then pushed to PushGateway which Prometheus scrapes.

### Problem 2: InMemory Storage Lost Metrics Between Requests

**Cause**: Using `Prometheus\Storage\InMemory()` created separate ephemeral registries for each request. Middleware wrote metrics to one registry; the `/metrics` endpoint read from a different empty registry.

**Solution**: PushGateway acts as a persistent metric buffer. All requests push to the same endpoint, and Prometheus scrapes them consistently.

### Problem 3: Prometheus Showing "Empty Query Result"

**Cause**: Metrics were never reaching Prometheus because:

-   Middleware couldn't connect to Redis (extension missing)
-   `/metrics` endpoint returned empty data
-   Prometheus had no valid scrape target

**Solution**:

-   Added PushGateway to docker-compose
-   Updated Prometheus config to scrape PushGateway (`:9091`)
-   Modified middleware to push metrics after each request

### Problem 4: Cannot Reach `http://pushgateway:9091` from Browser

**Cause**: Docker hostnames (`pushgateway`, `prometheus`) only resolve inside the Docker network. From Windows host, use `localhost` instead.

**Solution**: Access services from browser using:

-   `http://localhost:9091` (not `pushgateway:9091`)
-   `http://localhost:9090` (not `prometheus`)
-   `http://localhost:3000` (not `grafana`)

---

## Setup Instructions

### Step 1: Update docker-compose.yml

Add the PushGateway service and ensure Redis is running:

```yaml
version: "3.8"
services:
    redis:
        image: redis:7-alpine
        container_name: redis
        ports:
            - "6379:6379"
        restart: unless-stopped

    prometheus:
        image: prom/prometheus:latest
        container_name: prometheus
        ports:
            - "9090:9090"
        volumes:
            - ./prometheus.yml:/etc/prometheus/prometheus.yml
            - prom_data:/prometheus
        command:
            - "--config.file=/etc/prometheus/prometheus.yml"
        restart: unless-stopped

    pushgateway:
        image: prom/pushgateway:latest
        container_name: pushgateway
        ports:
            - "9091:9091"
        restart: unless-stopped

    grafana:
        image: grafana/grafana:latest
        container_name: grafana
        ports:
            - "3000:3000"
        volumes:
            - grafana_data:/var/lib/grafana
        restart: unless-stopped
        environment:
            - GF_SECURITY_ADMIN_PASSWORD=admin
            - GF_SECURITY_ADMIN_USER=admin

volumes:
    prom_data:
    grafana_data:
```

### Step 2: Update prometheus.yml

Configure Prometheus to scrape the PushGateway:

```yaml
global:
    scrape_interval: 15s

scrape_configs:
    - job_name: "pushgateway"
      honor_labels: true
      static_configs:
          - targets: ["pushgateway:9091"]
```

**Explanation:**

-   `honor_labels: true` preserves the `job="laravel"` label from pushed metrics
-   Prometheus scrapes PushGateway every 15 seconds
-   `pushgateway:9091` is resolvable inside Docker network

### Step 3: Update CollectMetrics Middleware

File: `app/Http/Middleware/CollectMetrics.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use PrometheusPushGateway\PushGateway;

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
        $histogram->observe($duration, [
            $request->method(),
            $request->path()
        ]);

        // Push metrics to PushGateway so Prometheus can scrape them
        // Use pushAdd to accumulate counters across requests
        try {
            $pushGateway = new PushGateway('http://pushgateway:9091');
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
```

**Key Points:**

-   `pushgateway:9091` is correct because middleware runs inside Laravel container (or as a process that can reach Docker network on Windows)
-   `pushAdd()` accumulates metrics instead of replacing them
-   Try-catch prevents request failures if PushGateway is down
-   Metrics include method, endpoint, status code, and response time

### Step 4: Register Middleware Globally

File: `bootstrap/app.php`

```php
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

        // Apply metrics collection to ALL requests
        $middleware->append(\App\Http\Middleware\CollectMetrics::class);

        $middleware->alias([
            'isAdmin' => \App\Http\Middleware\IsAdmin::class,
            'collectMetrics' => \App\Http\Middleware\CollectMetrics::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

**Important:** The `append()` method makes the middleware run on every request.

### Step 5: Composer Dependencies

Ensure these packages are installed:

```bash
composer require promphp/prometheus_client_php promphp/prometheus_push_gateway_php
```

Your `composer.json` should include:

```json
{
    "require": {
        "promphp/prometheus_client_php": "^2.0",
        "promphp/prometheus_push_gateway_php": "^1.0"
    }
}
```

### Step 6: Start Services

```bash
# Stop any running containers
docker-compose down

# Start fresh
docker-compose up -d

# In another terminal, start Laravel
php artisan serve --port=8000
```

**Verify containers are running:**

```bash
docker ps
```

Expected output:

```
CONTAINER ID   IMAGE                    COMMAND                 CREATED         STATUS       PORTS
abc123         prom/prometheus:latest   "/prometheus --c..."   2 minutes ago   Up 2 min     0.0.0.0:9090->9090/tcp
def456         grafana/grafana:latest   "/run.sh"              2 minutes ago   Up 2 min     0.0.0.0:3000->3000/tcp
ghi789         prom/pushgateway:latest  "/pushgateway ..."     2 minutes ago   Up 2 min     0.0.0.0:9091->9091/tcp
jkl012         redis:7-alpine           "redis-server"         2 minutes ago   Up 2 min     0.0.0.0:6379->6379/tcp
```

---

## Verification Steps

### 1. Check PushGateway UI

Open http://localhost:9091 in your browser. You should see:

-   A list of pushed metrics
-   Metrics starting with `laravel_http_` should appear after you browse your site

### 2. Check Prometheus Targets

1. Open http://localhost:9090
2. Go to **Status** → **Targets**
3. You should see:
    - **pushgateway:9091** → state **UP** (Green)

If it shows RED/DOWN, restart containers:

```bash
docker-compose restart prometheus
```

### 3. Query Metrics in Prometheus

In Prometheus UI (http://localhost:9090), run these queries:

**Total requests by status code:**

```promql
sum(rate(laravel_http_requests_total[5m])) by (status)
```

**Total requests by endpoint:**

```promql
sum(rate(laravel_http_requests_total[5m])) by (endpoint)
```

**Total requests by HTTP method:**

```promql
sum(rate(laravel_http_requests_total[5m])) by (method)
```

**Average response time (seconds):**

```promql
rate(laravel_http_response_time_seconds_sum[5m]) / rate(laravel_http_response_time_seconds_count[5m])
```

All queries should return data points (not "Empty query result").

### 4. Connect Grafana to Prometheus

1. Open http://localhost:3000
2. Login with: `admin` / `admin`
3. Go to **Connections** → **Data Sources**
4. Add **Prometheus**:
    - **URL**: `http://prometheus:9090` (internal Docker hostname)
    - Click **Save & test** → should show "Data source is working"

### 5. Create Grafana Dashboard

1. Go to **Dashboards** → **New** → **New Dashboard**
2. Click **Add Panel**
3. Add your first query in the **Metrics** field:
    ```promql
    sum(rate(laravel_http_requests_total[5m])) by (status)
    ```
4. Set:
    - **Legend**: `{{ status }}`
    - **Panel Title**: "Requests by Status Code"
5. Click **Apply**

Repeat for other metrics to build a complete dashboard.

---

## Grafana Query Examples

### 1. Requests Per Second (All)

```promql
sum(rate(laravel_http_requests_total[5m]))
```

### 2. Requests Per Second by Status Code

```promql
sum(rate(laravel_http_requests_total[5m])) by (status)
```

### 3. Requests Per Second by Endpoint

```promql
sum(rate(laravel_http_requests_total[5m])) by (endpoint)
```

### 4. Requests Per Second by HTTP Method

```promql
sum(rate(laravel_http_requests_total[5m])) by (method)
```

### 5. 404 Errors Only

```promql
sum(rate(laravel_http_requests_total{status="404"}[5m]))
```

### 6. Average Response Time

```promql
rate(laravel_http_response_time_seconds_sum[5m]) / rate(laravel_http_response_time_seconds_count[5m])
```

### 7. 95th Percentile Response Time

```promql
histogram_quantile(0.95, rate(laravel_http_response_time_seconds_bucket[5m]))
```

### 8. Total Requests (Counter)

```promql
laravel_http_requests_total
```

---

## Environment Variables (.env)

No additional `.env` changes are required. The setup uses Docker's internal networking and host port mappings.

If you want to customize PushGateway URL (optional), you can add to your `.env`:

```env
PROMETHEUS_PUSHGATEWAY_URL=http://pushgateway:9091
```

Then update the middleware:

```php
$pushGateway = new PushGateway(env('PROMETHEUS_PUSHGATEWAY_URL', 'http://pushgateway:9091'));
```

---

## Troubleshooting

### Issue: Prometheus target shows "DOWN"

**Solution:**

```bash
docker-compose logs prometheus
docker-compose restart prometheus pushgateway
```

### Issue: No data in Prometheus queries

**Check:**

1. Browse your Laravel app to generate traffic
2. Wait 15-30 seconds (Prometheus scrape interval)
3. Verify PushGateway has metrics: http://localhost:9091
4. Check middleware logs: `php artisan tinker` → `dd(collect(glob('storage/logs/*.log'))->last());`

### Issue: "Empty query result" in Prometheus

**Cause:** No metrics pushed yet or wrong query syntax

**Fix:**

-   Generate traffic to your site (visit home, products, etc.)
-   Use correct query syntax with `sum()` before `by()`
-   Example: `sum(rate(laravel_http_requests_total[5m])) by (status)`

### Issue: Cannot reach `http://pushgateway:9091` from browser

**Solution:** Use `http://localhost:9091` instead. `pushgateway` hostname only works inside Docker network.

### Issue: Middleware crashes app

**Check logs:**

```bash
php artisan serve
# Look for errors in terminal output
```

If PushGateway is down, middleware silently ignores errors (try-catch), so requests still work.

### Issue: Prometheus scraping `localhost:8000` but getting 404

**Context:** If you kept the Laravel scrape job in prometheus.yml:

-   `localhost:8000` won't work from inside Docker
-   Use `host.docker.internal:8000` (Windows/Mac) or `172.17.0.1:8000` (Linux)
-   **Recommended:** Only scrape PushGateway; remove Laravel scrape job

---

## Optional: Keep `/metrics` Endpoint (Manual Inspection)

If you want to manually inspect raw metrics from your browser:

File: `routes/web.php`

```php
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use Prometheus\RenderTextFormat;

Route::get('/metrics', function () {
    // This returns empty because InMemory doesn't persist
    // Use PushGateway UI (http://localhost:9091) instead
    $registry = new CollectorRegistry(new InMemory());
    $renderer = new RenderTextFormat();
    $result = $renderer->render($registry->getMetricFamilySamples());

    return response($result, 200)
        ->header('Content-Type', RenderTextFormat::MIME_TYPE);
});
```

**Note:** This endpoint will show empty data because it creates a fresh InMemory registry. For real metrics, use http://localhost:9091 (PushGateway).

---

## Metrics Explained

### `laravel_http_requests_total`

-   **Type:** Counter
-   **Labels:** `method`, `endpoint`, `status`
-   **Example:** How many GET requests to `/products` returned 200 status

### `laravel_http_response_time_seconds`

-   **Type:** Histogram with buckets
-   **Labels:** `method`, `endpoint`
-   **Example:** Response time distribution for different endpoints

---

## Quick Reference

| Service     | URL (Host)            | URL (Docker Network)             | Port |
| ----------- | --------------------- | -------------------------------- | ---- |
| Laravel     | http://127.0.0.1:8000 | http://host.docker.internal:8000 | 8000 |
| Prometheus  | http://localhost:9090 | http://prometheus:9090           | 9090 |
| Grafana     | http://localhost:3000 | http://grafana:3000              | 3000 |
| PushGateway | http://localhost:9091 | http://pushgateway:9091          | 9091 |
| Redis       | localhost:6379        | redis:6379                       | 6379 |

---

## What Gets Monitored

Every HTTP request to your Laravel app is tracked:

-   **HTTP Method** (GET, POST, PUT, DELETE, etc.)
-   **Endpoint Path** (/products, /category/electronics, etc.)
-   **Status Code** (200, 404, 500, etc.)
-   **Response Time** (in seconds, tracked in histogram buckets)

Metrics persist in PushGateway and are scraped by Prometheus every 15 seconds.

---

## Next Steps

1. **Create Custom Dashboards**: Import or create pre-built dashboards in Grafana
2. **Set Alerts**: Configure alert rules in Prometheus for error rates, slow responses
3. **Monitor Performance**: Track which endpoints are slowest
4. **Optimize**: Use metrics to identify bottlenecks in your Laravel app

---

## Summary

-   **Problem**: Laravel app had no monitoring; Prometheus/Grafana setup was broken due to missing PHP Redis extension
-   **Solution**: Switched to PushGateway for metric collection; metrics are now persistent and visible in Grafana
-   **Architecture**: Laravel middleware → PushGateway (persistent) → Prometheus (scrapes) → Grafana (visualizes)
-   **Status**: ✅ Working — browse your site and see metrics in Grafana within 30 seconds

---

**Happy monitoring!** 📊
