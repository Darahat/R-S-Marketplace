# Complete Monitoring & Load Testing Documentation

## Laravel Application Performance Monitoring & Load Testing with Prometheus, Grafana, PushGateway & K6

---

## Table of Contents

1. [Overview](#overview)
2. [System Architecture](#system-architecture)
3. [How Everything Connects](#how-everything-connects)
4. [Problems Faced & Solutions](#problems-faced--solutions)
5. [Complete Setup Guide](#complete-setup-guide)
6. [Application Monitoring Setup](#application-monitoring-setup)
7. [Load Testing Setup](#load-testing-setup)
8. [Unified Monitoring Workflow](#unified-monitoring-workflow)
9. [Grafana Dashboard Configuration](#grafana-dashboard-configuration)
10. [Troubleshooting Guide](#troubleshooting-guide)
11. [Best Practices](#best-practices)
12. [Quick Reference](#quick-reference)

---

## Overview

This documentation covers the **complete monitoring and load testing infrastructure** for the R&S Marketplace Laravel application. The system provides:

### Application Monitoring (Production/Development)

-   **Real-time tracking** of HTTP requests, response times, and error rates
-   **Metrics persistence** using PushGateway
-   **Prometheus scraping** every 15 seconds
-   **Grafana visualization** with custom dashboards

### Load Testing (Performance Validation)

-   **K6 load testing** framework for simulating user traffic
-   **Dual metrics export**: InfluxDB (historical) + Prometheus (real-time)
-   **Live monitoring** during test execution
-   **Threshold validation** for SLA compliance

### Integration Benefits

-   **Correlate** production metrics with load test results
-   **Baseline** performance before/after deployments
-   **Identify** bottlenecks under different load conditions
-   **Validate** application behavior under stress

---

## System Architecture

### Complete Infrastructure Diagram

```
┌──────────────────────────────────────────────────────────────────────────┐
│                    Your Browser (Windows Host)                           │
│  • Grafana Dashboard:     http://localhost:3000                          │
│  • Prometheus UI:         http://localhost:9090                          │
│  • PushGateway Metrics:   http://localhost:9091                          │
│  • InfluxDB UI:           http://localhost:8888 (Chronograf)             │
└──────────────────────────────────────────────────────────────────────────┘
                                      ↓
┌──────────────────────────────────────────────────────────────────────────┐
│                          Docker Network                                  │
│                                                                           │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │                    Monitoring Stack                              │   │
│  │                                                                  │   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐            │   │
│  │  │ Prometheus  │  │   Grafana   │  │ PushGateway │            │   │
│  │  │   :9090     │◄─┤    :3000    │◄─┤    :9091    │            │   │
│  │  └─────────────┘  └─────────────┘  └─────────────┘            │   │
│  │         ↓                ↓                  ↑                   │   │
│  │     Scrapes        Queries Both         Receives               │   │
│  │    Targets        DataSources         App Metrics              │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│                                                                           │
│  ┌──────────────────────────────────────────────────────────────────┐   │
│  │                    Load Testing Stack                            │   │
│  │                                                                  │   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐            │   │
│  │  │     K6      │──┤  InfluxDB   │  │ Chronograf  │            │   │
│  │  │   :6565     │  │   :8086     │──┤   :8888     │            │   │
│  │  └─────────────┘  └─────────────┘  └─────────────┘            │   │
│  │         │                                                       │   │
│  │    Exposes Metrics                                             │   │
│  │    for Prometheus                                              │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│                                                                           │
│  ┌─────────────┐                                                         │
│  │    Redis    │  (Optional for Laravel caching)                         │
│  │   :6379     │                                                         │
│  └─────────────┘                                                         │
└──────────────────────────────────────────────────────────────────────────┘
                                      ↓
┌──────────────────────────────────────────────────────────────────────────┐
│              Laravel Application (Host: Windows)                         │
│              http://127.0.0.1:8000                                       │
│                                                                           │
│  • CollectMetrics Middleware → Pushes to PushGateway:9091               │
│  • Handles Production Traffic                                           │
│  • Target for K6 Load Tests                                             │
└──────────────────────────────────────────────────────────────────────────┘
```

---

## How Everything Connects

### Data Flow Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         PRODUCTION MONITORING FLOW                       │
└─────────────────────────────────────────────────────────────────────────┘

User Request → Laravel App → CollectMetrics Middleware
                                      ↓
                           Records: Method, Path, Status, Duration
                                      ↓
                        Pushes to PushGateway (Port 9091)
                                      ↓
                      Prometheus Scrapes Every 15s
                                      ↓
                      Stores in Time Series Database
                                      ↓
                         Grafana Queries & Visualizes


┌─────────────────────────────────────────────────────────────────────────┐
│                         LOAD TESTING FLOW                                │
└─────────────────────────────────────────────────────────────────────────┘

K6 Test Script → Simulates Virtual Users → Hits Laravel App
                                                      ↓
                            Laravel Responds (monitored by CollectMetrics)
                                                      ↓
                            K6 Records Test Metrics
                                   ↓              ↓
                         InfluxDB (Historical)  Prometheus (Real-time)
                                   ↓              ↓
                                Grafana Shows Both Datasets
                                         ↓
                        K6 Dashboard (Live) + Laravel Dashboard (App Metrics)


┌─────────────────────────────────────────────────────────────────────────┐
│                         INTEGRATED ANALYSIS                              │
└─────────────────────────────────────────────────────────────────────────┘

During Load Test:
  • K6 metrics show: Load pattern, virtual users, test response times
  • Laravel metrics show: Actual server load, endpoint performance, errors
  • Cross-reference: Does K6 latency match Laravel processing time?
  • Identify: Bottlenecks, memory leaks, database issues

```

### Component Dependencies

```
Component          Depends On              Provides To
─────────────────────────────────────────────────────────────────────────
Laravel App        None                    Metrics to PushGateway
                                          API endpoints to K6

PushGateway        None                    Metrics to Prometheus
                   (receives from Laravel)

Prometheus         PushGateway             Data to Grafana
                   K6 (during tests)

K6                 Laravel App             Metrics to InfluxDB
                                          Metrics to Prometheus

InfluxDB           K6                      Data to Grafana

Grafana            Prometheus              Dashboards to User
                   InfluxDB

Redis              None                    Cache to Laravel (optional)
```

---

## Problems Faced & Solutions

### Problem 1: "Class Redis not found" ❌

**Scenario**: Initial attempt to use Prometheus Redis storage adapter  
**Symptoms**:

```
Prometheus\Exception\StorageException
vendor\promphp\prometheus_client_php\src\Prometheus\Storage\Redis.php
Class 'Redis' not found
```

**Root Cause**:

-   Prometheus PHP client requires the `phpredis` extension (`\Redis`)
-   Predis (composer package) cannot replace the native PHP extension
-   Windows PHP installation didn't include the Redis extension

**Attempted Solutions** ❌:

1. Installing Predis via Composer → Failed (different interface)
2. Using `\Redis()` class directly → Failed (extension not loaded)
3. Configuring Laravel Redis facade → Failed (incompatible with Prometheus)

**Final Solution** ✅:

-   Switched to **PushGateway** architecture
-   Metrics collected in-memory per request
-   Pushed to PushGateway for persistence
-   Prometheus scrapes from PushGateway

**Lesson Learned**: PushGateway is ideal for Windows development where installing PHP extensions is difficult

---

### Problem 2: InMemory Storage Lost Metrics Between Requests ❌

**Scenario**: Using `Prometheus\Storage\InMemory()` for metric storage  
**Symptoms**:

```
http://127.0.0.1:8000/metrics → Shows empty output
Prometheus queries → "Empty query result"
Grafana panels → "No data"
```

**Root Cause**:

-   `InMemory()` creates isolated registry per PHP process
-   Middleware writes metrics to Registry A
-   `/metrics` endpoint reads from Registry B
-   No shared state between requests
-   Metrics vanish after request completes

**Why This Happened**:

```php
// Each request creates SEPARATE registry
public function handle($request, Closure $next) {
    $registry = new CollectorRegistry(new InMemory()); // NEW registry
    $counter->inc([...]); // Writes to Registry #1
    return $response;
} // Registry #1 destroyed

Route::get('/metrics', function() {
    $registry = new CollectorRegistry(new InMemory()); // DIFFERENT registry
    return $registry->getMetrics(); // Reads Registry #2 (empty!)
});
```

**Solution** ✅:

-   PushGateway acts as persistent external storage
-   Middleware pushes to PushGateway after each request
-   Prometheus scrapes PushGateway (not Laravel)
-   Metrics accumulate across requests

---

### Problem 3: Prometheus Showing "Empty Query Result" ❌

**Scenario**: Prometheus configured but no data available  
**Symptoms**:

```
Query: laravel_http_requests_total
Result: Empty query result (no data points)

Prometheus Targets → Laravel target DOWN
```

**Root Causes**:

1. Middleware couldn't connect to Redis (extension missing)
2. `/metrics` endpoint returned empty Prometheus format
3. No valid scrape target configured
4. Metrics never reached Prometheus

**Debugging Steps Taken**:

```powershell
# Checked metrics endpoint
curl http://127.0.0.1:8000/metrics
# Result: Empty or error

# Checked Prometheus targets
http://localhost:9090/targets
# Result: All targets DOWN

# Checked middleware logs
php artisan serve
# Result: Redis connection errors
```

**Solution** ✅:

1. Added PushGateway to `docker-compose.yml`
2. Updated `prometheus.yml` to scrape PushGateway
3. Modified middleware to push metrics
4. Removed direct Laravel scrape job

---

### Problem 4: Cannot Reach Docker Services from Browser ❌

**Scenario**: Trying to access services using Docker hostnames  
**Symptoms**:

```
http://pushgateway:9091 → Site can't be reached
http://prometheus → ERR_NAME_NOT_RESOLVED
Grafana datasource http://prometheus:9090 → Working ✅
```

**Root Cause**:

-   Docker compose creates internal network
-   Hostnames (`prometheus`, `pushgateway`, `grafana`) only resolve inside Docker
-   Windows host cannot resolve these names
-   Confusion between host URLs and container URLs

**Understanding**:

```
From Windows Host (Browser):
  ✅ http://localhost:9090      (Port mapping)
  ❌ http://prometheus:9090     (No DNS resolution)

From Docker Container (e.g., Grafana):
  ❌ http://localhost:9090      (Container's own localhost)
  ✅ http://prometheus:9090     (Docker DNS)

From Laravel Middleware (Host Process):
  ✅ http://localhost:9091      (PushGateway via port mapping)
  ❌ http://pushgateway:9091    (Can't resolve if not in Docker network)
```

**Solution** ✅:

-   Use `localhost` from Windows host browser
-   Use Docker hostnames in config files (prometheus.yml, grafana datasource)
-   Document both URL formats clearly

---

### Problem 5: K6 Metrics Not Appearing in Prometheus ❌

**Scenario**: K6 tests running but Prometheus shows no k6 metrics  
**Symptoms**:

```
Query: k6_http_reqs → No data
Prometheus Targets → k6 target missing or DOWN
K6 dashboard in Grafana → Empty panels
```

**Root Cause**:

1. K6 container not exposing Prometheus metrics endpoint
2. Prometheus not configured to scrape K6
3. K6 test finishes before Prometheus scrapes

**Solution** ✅:

1. Updated K6 to expose metrics on port 6565
2. Added K6 scrape job to `prometheus.yml`
3. K6 metrics available during test execution
4. Historical data remains in InfluxDB

---

## Complete Setup Guide

### Prerequisites

-   Docker Desktop installed and running
-   PHP 8.1+ with Composer
-   Laravel application
-   Git (for version control)

### Step-by-Step Installation

#### Step 1: Update docker-compose.yml

Create complete monitoring stack:

```yaml
version: "3.8"

services:
    # Application monitoring - Persistent metric storage
    pushgateway:
        image: prom/pushgateway:latest
        container_name: pushgateway
        ports:
            - "9091:9091"
        restart: unless-stopped
        networks:
            - monitoring

    # Metrics collection and querying
    prometheus:
        image: prom/prometheus:latest
        container_name: prometheus
        ports:
            - "9090:9090"
        volumes:
            - ./prometheus.yml:/etc/prometheus/prometheus.yml
            - ./prometheus-rules.yml:/etc/prometheus/rules.yml # Optional: alerting
            - prom_data:/prometheus
        command:
            - "--config.file=/etc/prometheus/prometheus.yml"
            - "--storage.tsdb.retention.time=30d" # Keep data for 30 days
        restart: unless-stopped
        networks:
            - monitoring
        depends_on:
            - pushgateway

    # Visualization platform
    grafana:
        image: grafana/grafana:latest
        container_name: grafana
        ports:
            - "3000:3000"
        volumes:
            - grafana_data:/var/lib/grafana
            - ./grafana/provisioning:/etc/grafana/provisioning # Auto-provision datasources
            - ./grafana/dashboards:/var/lib/grafana/dashboards
        restart: unless-stopped
        environment:
            - GF_SECURITY_ADMIN_PASSWORD=admin
            - GF_SECURITY_ADMIN_USER=admin
            - GF_INSTALL_PLUGINS= # Add plugins if needed
        networks:
            - monitoring
        depends_on:
            - prometheus
            - influxdb

    # Load testing metrics storage
    influxdb:
        image: influxdb:1.8-alpine
        container_name: influxdb
        ports:
            - "8086:8086"
        volumes:
            - influxdb_data:/var/lib/influxdb
        environment:
            - INFLUXDB_DB=k6
            - INFLUXDB_HTTP_AUTH_ENABLED=false
        restart: unless-stopped
        networks:
            - monitoring

    # InfluxDB UI
    chronograf:
        image: chronograf:1.10-alpine
        container_name: chronograf
        ports:
            - "8888:8888"
        environment:
            - INFLUXDB_URL=http://influxdb:8086
        restart: unless-stopped
        networks:
            - monitoring
        depends_on:
            - influxdb

    # Load testing tool
    k6:
        image: grafana/k6:latest
        container_name: k6
        ports:
            - "6565:6565" # Prometheus metrics endpoint
        volumes:
            - ./k6-scripts:/scripts
        environment:
            - K6_PROMETHEUS_RW_SERVER_URL=http://prometheus:9090/api/v1/write
            - K6_OUT=influxdb=http://influxdb:8086/k6
        networks:
            - monitoring
        depends_on:
            - influxdb
        # No auto-start - run tests manually
        profiles:
            - tools

    # Optional: Redis for Laravel caching
    redis:
        image: redis:7-alpine
        container_name: redis
        ports:
            - "6379:6379"
        restart: unless-stopped
        networks:
            - monitoring

networks:
    monitoring:
        driver: bridge

volumes:
    prom_data:
    grafana_data:
    influxdb_data:
```

#### Step 2: Configure Prometheus (prometheus.yml)

```yaml
global:
    scrape_interval: 15s # Scrape targets every 15 seconds
    evaluation_interval: 15s # Evaluate rules every 15 seconds
    external_labels:
        cluster: "rs-marketplace-local"
        environment: "development"

# Alerting configuration (optional)
alerting:
    alertmanagers:
        - static_configs:
              - targets: []
          # - targets: ['alertmanager:9093']

# Load rules once and periodically evaluate them
rule_files:
    # - 'prometheus-rules.yml'

scrape_configs:
    # Laravel application metrics via PushGateway
    - job_name: "pushgateway"
      honor_labels: true # Preserve job labels from pushed metrics
      static_configs:
          - targets: ["pushgateway:9091"]
            labels:
                service: "laravel-app"

    # K6 load test metrics (only available during test execution)
    - job_name: "k6"
      honor_labels: true
      static_configs:
          - targets: ["k6:6565"]
            labels:
                service: "load-testing"

    # Optional: Direct Laravel scraping (not recommended with PushGateway)
    # - job_name: 'laravel-direct'
    #   metrics_path: '/metrics'
    #   static_configs:
    #     - targets: ['host.docker.internal:8000']
```

#### Step 3: Create Grafana Provisioning Files

**File**: `grafana/provisioning/datasources/datasources.yaml`

```yaml
apiVersion: 1

datasources:
    # Prometheus - Primary datasource for real-time metrics
    - name: Prometheus
      type: prometheus
      access: proxy
      url: http://prometheus:9090
      isDefault: true
      jsonData:
          timeInterval: 15s
          queryTimeout: 60s
      editable: false

    # InfluxDB - K6 load test metrics
    - name: influxdb
      type: influxdb
      access: proxy
      url: http://influxdb:8086
      database: k6
      isDefault: false
      jsonData:
          timeInterval: 5s
      editable: false
```

#### Step 4: Create CollectMetrics Middleware

**File**: `app/Http/Middleware/CollectMetrics.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use PrometheusPushGateway\PushGateway;
use Symfony\Component\HttpFoundation\Response;

class CollectMetrics
{
    /**
     * Handle an incoming request and collect metrics.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        // Process request
        $response = $next($request);

        $duration = microtime(true) - $start;

        // Create in-memory registry (ephemeral, will be pushed)
        $registry = new CollectorRegistry(new InMemory());

        // Counter: Total HTTP requests
        $counter = $registry->getOrRegisterCounter(
            'laravel',  // namespace
            'http_requests_total',  // metric name
            'Total HTTP requests',  // help text
            ['method', 'endpoint', 'status']  // labels
        );

        $counter->inc([
            $request->method(),
            $this->normalizeEndpoint($request->path()),
            (string) $response->getStatusCode()
        ]);

        // Histogram: Response time distribution
        $histogram = $registry->getOrRegisterHistogram(
            'laravel',
            'http_response_time_seconds',
            'HTTP response time in seconds',
            ['method', 'endpoint'],
            [0.005, 0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1.0, 2.5, 5.0, 10.0]  // buckets
        );

        $histogram->observe($duration, [
            $request->method(),
            $this->normalizeEndpoint($request->path())
        ]);

        // Gauge: Current memory usage
        $memoryGauge = $registry->getOrRegisterGauge(
            'laravel',
            'memory_usage_bytes',
            'Current memory usage in bytes',
            ['type']
        );

        $memoryGauge->set(memory_get_usage(true), ['allocated']);
        $memoryGauge->set(memory_get_peak_usage(true), ['peak']);

        // Push to PushGateway
        try {
            $pushGateway = new PushGateway(
                env('PROMETHEUS_PUSHGATEWAY_URL', 'http://localhost:9091')
            );

            $pushGateway->pushAdd($registry, 'laravel', [
                'instance' => gethostname(),
                'application' => env('APP_NAME', 'R&S Marketplace')
            ]);
        } catch (\Throwable $e) {
            // Silently fail - don't break application flow
            logger()->warning('Failed to push metrics to PushGateway', [
                'error' => $e->getMessage()
            ]);
        }

        return $response;
    }

    /**
     * Normalize endpoint to reduce cardinality
     * e.g., /products/123 → /products/{id}
     */
    private function normalizeEndpoint(string $path): string
    {
        // Replace numeric IDs with placeholder
        $path = preg_replace('/\/\d+/', '/{id}', $path);

        // Replace UUIDs with placeholder
        $path = preg_replace(
            '/\/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i',
            '/{uuid}',
            $path
        );

        return $path;
    }
}
```

#### Step 5: Register Middleware

**File**: `bootstrap/app.php`

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
        // CSRF exclusions
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
            'metrics',  // Exclude metrics endpoint from CSRF
        ]);

        // Global middleware - applies to ALL requests
        $middleware->append(\App\Http\Middleware\CollectMetrics::class);

        // Aliased middleware
        $middleware->alias([
            'isAdmin' => \App\Http\Middleware\IsAdmin::class,
            'collectMetrics' => \App\Http\Middleware\CollectMetrics::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

#### Step 6: Install PHP Dependencies

```bash
composer require promphp/prometheus_client_php promphp/prometheus_push_gateway_php
```

Update `composer.json`:

```json
{
    "require": {
        "php": "^8.1",
        "laravel/framework": "^11.0",
        "promphp/prometheus_client_php": "^2.10",
        "promphp/prometheus_push_gateway_php": "^1.0"
    }
}
```

#### Step 7: Create K6 Test Scripts

**File**: `k6-scripts/smoke.js`

```javascript
import http from "k6/http";
import { check, group, sleep } from "k6";
import { Rate, Trend, Counter } from "k6/metrics";

// Custom metrics
const errorRate = new Rate("errors");
const responseTime = new Trend("response_time");
const requestCount = new Counter("request_count");

// Test configuration
export const options = {
    stages: [
        { duration: "30s", target: 5 }, // Ramp up to 5 users
        { duration: "1m", target: 5 }, // Stay at 5 users
        { duration: "30s", target: 0 }, // Ramp down
    ],
    thresholds: {
        http_req_duration: ["p(95)<2000"], // 95% of requests < 2s
        http_req_failed: ["rate<0.01"], // Error rate < 1%
        errors: ["rate<0.1"], // Custom error rate < 10%
    },
    tags: {
        test_type: "smoke",
        environment: "local",
    },
};

const BASE_URL = "http://host.docker.internal:8000";

export default function () {
    // Homepage
    group("Homepage", function () {
        const res = http.get(`${BASE_URL}/`);

        check(res, {
            "homepage status 200": (r) => r.status === 200,
            "homepage loads < 2s": (r) => r.timings.duration < 2000,
        }) || errorRate.add(1);

        responseTime.add(res.timings.duration);
        requestCount.add(1);
    });

    sleep(1);

    // Product listing
    group("Products", function () {
        const res = http.get(`${BASE_URL}/products`);

        check(res, {
            "products status 200": (r) => r.status === 200,
        }) || errorRate.add(1);

        responseTime.add(res.timings.duration);
        requestCount.add(1);
    });

    sleep(1);

    // Category page
    group("Category", function () {
        const res = http.get(`${BASE_URL}/category/electronics`);

        check(res, {
            "category status 200": (r) => r.status === 200,
        }) || errorRate.add(1);

        responseTime.add(res.timings.duration);
        requestCount.add(1);
    });

    sleep(Math.random() * 3); // Random think time
}

export function handleSummary(data) {
    return {
        stdout: textSummary(data, { indent: " ", enableColors: true }),
    };
}
```

#### Step 8: Environment Configuration

**File**: `.env` (add these lines)

```env
# Prometheus Configuration
PROMETHEUS_PUSHGATEWAY_URL=http://localhost:9091
PROMETHEUS_ENABLED=true

# Application Monitoring
APP_MONITORING_ENABLED=true
```

#### Step 9: Start the Complete Stack

```powershell
# Stop any existing containers
docker-compose down -v

# Start monitoring infrastructure
docker-compose up -d

# Wait for services to be ready
Start-Sleep -Seconds 10

# Verify all containers are running
docker ps

# Expected output:
# - prometheus (port 9090)
# - grafana (port 3000)
# - pushgateway (port 9091)
# - influxdb (port 8086)
# - chronograf (port 8888)
# - redis (port 6379)

# Start Laravel application
php artisan serve --port=8000
```

---

## Application Monitoring Setup

### Metrics Collection Process

```
1. User makes HTTP request to Laravel
   ↓
2. CollectMetrics middleware intercepts request
   ↓
3. Middleware records:
   - Request method (GET, POST, etc.)
   - Normalized endpoint (/products/{id})
   - Response status code (200, 404, 500)
   - Response time (in seconds)
   - Memory usage
   ↓
4. Metrics pushed to PushGateway
   ↓
5. Prometheus scrapes PushGateway every 15s
   ↓
6. Data stored in Prometheus time-series database
   ↓
7. Grafana queries Prometheus and visualizes metrics
```

### Available Laravel Metrics

| Metric Name                          | Type      | Labels                   | Description                   |
| ------------------------------------ | --------- | ------------------------ | ----------------------------- |
| `laravel_http_requests_total`        | Counter   | method, endpoint, status | Total HTTP requests           |
| `laravel_http_response_time_seconds` | Histogram | method, endpoint         | Response time distribution    |
| `laravel_memory_usage_bytes`         | Gauge     | type                     | Memory usage (allocated/peak) |

### PromQL Queries for Laravel

```promql
# Total requests per second
sum(rate(laravel_http_requests_total[5m]))

# Requests by status code
sum(rate(laravel_http_requests_total[5m])) by (status)

# Requests by endpoint
sum(rate(laravel_http_requests_total[5m])) by (endpoint)

# Average response time
rate(laravel_http_response_time_seconds_sum[5m]) /
rate(laravel_http_response_time_seconds_count[5m])

# P95 response time
histogram_quantile(0.95,
    rate(laravel_http_response_time_seconds_bucket[5m])
)

# Error rate (4xx + 5xx)
sum(rate(laravel_http_requests_total{status=~"4..|5.."}[5m])) /
sum(rate(laravel_http_requests_total[5m]))

# 404 errors
sum(rate(laravel_http_requests_total{status="404"}[5m]))

# Memory usage
laravel_memory_usage_bytes{type="allocated"}
```

---

## Load Testing Setup

### K6 Integration

K6 exports metrics to **both** InfluxDB and Prometheus:

**InfluxDB (Historical)**:

-   Complete test history
-   Detailed time-series data
-   Post-test analysis

**Prometheus (Real-time)**:

-   Live test monitoring
-   Correlate with Laravel metrics
-   Same Grafana instance

### Running Load Tests

```powershell
# Smoke test (light load, 2 minutes)
docker-compose run --rm k6 run /scripts/smoke.js

# Stress test (heavy load, defined in stress.js)
docker-compose run --rm k6 run /scripts/stress.js

# With custom VUs and duration
docker-compose run --rm k6 run --vus 10 --duration 5m /scripts/smoke.js

# With environment variables
docker-compose run --rm -e K6_VUS=20 k6 run /scripts/smoke.js

# Save results to file
docker-compose run --rm k6 run --out json=results.json /scripts/smoke.js
```

### K6 Metrics in Prometheus

| Metric                 | Type      | Description           |
| ---------------------- | --------- | --------------------- |
| `k6_vus`               | Gauge     | Current virtual users |
| `k6_vus_max`           | Gauge     | Max configured VUs    |
| `k6_http_reqs`         | Counter   | Total HTTP requests   |
| `k6_http_req_duration` | Histogram | Request duration      |
| `k6_http_req_failed`   | Counter   | Failed requests       |
| `k6_iterations`        | Counter   | Total iterations      |
| `k6_data_sent`         | Counter   | Bytes sent            |
| `k6_data_received`     | Counter   | Bytes received        |

### PromQL Queries for K6

```promql
# Current virtual users
k6_vus

# Requests per second
rate(k6_http_reqs[1m])

# P95 response time
histogram_quantile(0.95, rate(k6_http_req_duration_bucket[1m]))

# Error rate
sum(rate(k6_http_req_failed[1m])) / sum(rate(k6_http_reqs[1m]))

# Data throughput (MB/s)
rate(k6_data_received[1m]) / 1024 / 1024
```

---

## Unified Monitoring Workflow

### Complete Testing & Monitoring Procedure

#### Phase 1: Baseline Measurement (Before Load Test)

**Step 1**: Ensure monitoring stack is running

```powershell
docker-compose up -d prometheus grafana pushgateway influxdb
```

**Step 2**: Start Laravel application

```powershell
php artisan serve --port=8000
```

**Step 3**: Generate light production traffic

-   Browse homepage, products, categories manually
-   Or run a minimal K6 script (1-2 VUs)

**Step 4**: Observe baseline metrics in Grafana

-   Open http://localhost:3000
-   Go to Laravel Application Dashboard
-   Note baseline values:
    -   Requests/sec: ~5-10
    -   P95 response time: ~100-300ms
    -   Memory usage: ~50MB
    -   Error rate: 0%

#### Phase 2: Load Test Execution

**Step 5**: Open both dashboards in separate browser tabs

-   Tab 1: K6 Load Test Dashboard (Prometheus)
-   Tab 2: Laravel Application Dashboard

**Step 6**: Run K6 load test

```powershell
docker-compose run --rm k6 run /scripts/smoke.js
```

**Step 7**: Monitor in real-time (refresh every 5 seconds)

**K6 Dashboard shows**:

-   Virtual users ramping up: 0 → 5 → 5 → 0
-   K6 request rate increasing
-   K6-measured response times
-   Test-specific error rate

**Laravel Dashboard shows**:

-   Actual server request rate (should match K6)
-   Server-side response times
-   Endpoint-specific performance
-   Memory consumption
-   HTTP status distribution

**Step 8**: Compare metrics

| Metric        | K6 View                | Laravel View                | Expected Correlation         |
| ------------- | ---------------------- | --------------------------- | ---------------------------- |
| Request Rate  | k6_http_reqs           | laravel_http_requests_total | Should match ±10%            |
| Response Time | k6_http_req_duration   | laravel_http_response_time  | K6 slightly higher (network) |
| Error Rate    | k6_http_req_failed     | laravel{status="5.."}       | Should match exactly         |
| Load          | k6_vus (virtual users) | laravel request rate        | More VUs = More requests     |

#### Phase 3: Analysis

**Step 9**: Identify bottlenecks

Check for:

```promql
# Are slow endpoints slowing everything down?
topk(5,
    histogram_quantile(0.95,
        rate(laravel_http_response_time_seconds_bucket[5m])
    ) by (endpoint)
)

# Is memory growing during test?
increase(laravel_memory_usage_bytes[5m])

# Which status codes are most common?
sum(rate(laravel_http_requests_total[5m])) by (status)
```

**Step 10**: Document findings

-   Screenshot Grafana panels
-   Export PromQL query results
-   Note threshold violations from K6

#### Phase 4: Post-Test Review

**Step 11**: Check InfluxDB for detailed analysis

-   Open http://localhost:8888 (Chronograf)
-   View complete test history
-   Analyze specific time ranges

**Step 12**: Generate reports

```powershell
# Export Prometheus data
curl "http://localhost:9090/api/v1/query_range?query=laravel_http_requests_total&start=2025-01-01T00:00:00Z&end=2025-01-01T01:00:00Z&step=15s" > metrics-export.json
```

---

## Grafana Dashboard Configuration

### Dashboard 1: Laravel Application Monitoring

**Purpose**: Real-time production/development application metrics

**Panels**:

1. **Request Rate** (Time series)

```promql
sum(rate(laravel_http_requests_total[5m]))
```

2. **Response Time Percentiles** (Time series)

```promql
# P50
histogram_quantile(0.50, rate(laravel_http_response_time_seconds_bucket[5m]))
# P95
histogram_quantile(0.95, rate(laravel_http_response_time_seconds_bucket[5m]))
# P99
histogram_quantile(0.99, rate(laravel_http_response_time_seconds_bucket[5m]))
```

3. **HTTP Status Distribution** (Pie chart)

```promql
sum(rate(laravel_http_requests_total[5m])) by (status)
```

4. **Top Endpoints by Traffic** (Bar gauge)

```promql
topk(10, sum(rate(laravel_http_requests_total[5m])) by (endpoint))
```

5. **Error Rate** (Stat panel with threshold)

```promql
sum(rate(laravel_http_requests_total{status=~"5.."}[5m])) /
sum(rate(laravel_http_requests_total[5m])) * 100
```

6. **Memory Usage** (Time series)

```promql
laravel_memory_usage_bytes
```

**Alert Thresholds**:

-   Error rate > 5% → Warning
-   Error rate > 10% → Critical
-   P95 response time > 2000ms → Warning

### Dashboard 2: K6 Load Testing (Prometheus)

**Purpose**: Real-time load test monitoring

**Panels**:

1. **Virtual Users** (Time series)

```promql
k6_vus
```

2. **Request Rate** (Time series)

```promql
sum(rate(k6_http_reqs[1m]))
```

3. **Response Time Percentiles** (Time series)

```promql
histogram_quantile(0.50, sum(rate(k6_http_req_duration_bucket[1m])) by (le))
histogram_quantile(0.95, sum(rate(k6_http_req_duration_bucket[1m])) by (le))
histogram_quantile(0.99, sum(rate(k6_http_req_duration_bucket[1m])) by (le))
```

4. **Error Rate** (Gauge)

```promql
sum(rate(k6_http_req_failed[1m])) / sum(rate(k6_http_reqs[1m])) * 100
```

5. **Throughput** (Time series)

```promql
# Sent
rate(k6_data_sent[1m]) / 1024 / 1024
# Received
rate(k6_data_received[1m]) / 1024 / 1024
```

6. **Checks Success Rate** (Stat)

```promql
sum(rate(k6_checks{result="pass"}[1m])) / sum(rate(k6_checks[1m])) * 100
```

### Dashboard 3: Unified Load Test Analysis

**Purpose**: Correlate K6 and Laravel metrics

**Panels**:

1. **Request Rate Comparison**

```promql
# K6 perspective
sum(rate(k6_http_reqs[1m]))
# Laravel perspective
sum(rate(laravel_http_requests_total[1m]))
```

2. **Response Time Comparison**

```promql
# K6 P95
histogram_quantile(0.95, rate(k6_http_req_duration_bucket[1m]))
# Laravel P95
histogram_quantile(0.95, rate(laravel_http_response_time_seconds_bucket[1m]))
```

3. **System Health During Test**

```promql
# Memory
laravel_memory_usage_bytes
# Error rate
sum(rate(laravel_http_requests_total{status="500"}[1m]))
```

---

## Troubleshooting Guide

### Issue: No Data in Grafana

**Symptoms**:

-   Empty panels
-   "No data" message
-   Queries return no results

**Diagnosis Steps**:

```powershell
# 1. Check if Laravel is running
curl http://127.0.0.1:8000

# 2. Check if PushGateway has metrics
curl http://localhost:9091/metrics | Select-String "laravel"

# 3. Check Prometheus targets
# Browser: http://localhost:9090/targets
# All should be UP (green)

# 4. Query Prometheus directly
# Browser: http://localhost:9090
# Run: laravel_http_requests_total

# 5. Check Grafana datasource
# Browser: http://localhost:3000/datasources
# Test connection
```

**Solutions**:

1. **PushGateway shows no metrics**

```powershell
# Generate traffic
curl http://127.0.0.1:8000
curl http://127.0.0.1:8000/products

# Check Laravel logs
php artisan serve
# Look for "Failed to push metrics"
```

2. **Prometheus target DOWN**

```powershell
# Restart Prometheus
docker-compose restart prometheus

# Check Prometheus logs
docker-compose logs prometheus
```

3. **Grafana datasource error**

```powershell
# Restart Grafana
docker-compose restart grafana

# Re-provision datasources
docker-compose down grafana
docker-compose up -d grafana
```

### Issue: K6 Metrics Not Appearing

**Symptoms**:

-   K6 queries return empty
-   K6 target shows DOWN in Prometheus

**Diagnosis**:

```powershell
# 1. Check if K6 is exposing metrics (during test)
docker-compose run --rm k6 run /scripts/smoke.js &
curl http://localhost:6565/metrics

# 2. Check K6 container logs
docker-compose logs k6

# 3. Verify InfluxDB connection
docker-compose exec influxdb influx -execute 'SHOW DATABASES'
# Should show 'k6'
```

**Solutions**:

1. **K6 container exits immediately**

```yaml
# In docker-compose.yml, ensure k6 has:
profiles:
    - tools
# This prevents auto-start
```

2. **K6 can't reach Laravel**

```powershell
# Test connectivity from K6 container
docker-compose run --rm k6 sh -c "wget -O- http://host.docker.internal:8000"
```

3. **Metrics not persisting**

```powershell
# Ensure K6 environment variables are set
K6_OUT=influxdb=http://influxdb:8086/k6
```

### Issue: High Memory Usage

**Symptoms**:

-   Laravel memory gauge increasing
-   PHP memory limit errors

**Investigation**:

```promql
# Memory growth rate
rate(laravel_memory_usage_bytes{type="allocated"}[5m])

# Peak memory during test
max_over_time(laravel_memory_usage_bytes{type="peak"}[10m])
```

**Solutions**:

1. **Increase PHP memory limit**

```ini
; php.ini
memory_limit = 512M
```

2. **Check for memory leaks**

```php
// Add to middleware
logger()->info('Memory usage', [
    'current' => memory_get_usage(true),
    'peak' => memory_get_peak_usage(true)
]);
```

3. **Optimize queries**

```php
// Use chunking for large datasets
DB::table('products')->chunk(100, function ($products) {
    // Process
});
```

### Issue: Prometheus Storage Full

**Symptoms**:

-   Prometheus logs show disk space errors
-   Old metrics disappearing

**Solution**:

```yaml
# In docker-compose.yml
prometheus:
    command:
        - "--storage.tsdb.retention.time=30d"
        - "--storage.tsdb.retention.size=10GB"
```

### Issue: Cannot Access Services from Browser

**Problem**: `http://prometheus:9090` doesn't load

**Solution**: Use localhost

```
❌ http://prometheus:9090    (Docker internal hostname)
✅ http://localhost:9090     (Host port mapping)

❌ http://pushgateway:9091
✅ http://localhost:9091

❌ http://grafana:3000
✅ http://localhost:3000
```

**Exception**: Inside Grafana datasource config, use Docker hostnames:

```
Grafana datasource URL: http://prometheus:9090  ✅
(Grafana container can resolve 'prometheus')
```

---

## Best Practices

### 1. Metric Labeling Strategy

**DO**:

```php
// Normalize endpoints to reduce cardinality
'/products/123' → '/products/{id}'
'/users/abc-def-ghi' → '/users/{uuid}'
```

**DON'T**:

```php
// Avoid high cardinality labels
$counter->inc([$userId]);  // ❌ Unique per user
$counter->inc([$timestamp]);  // ❌ Unique per request
```

### 2. Prometheus Query Optimization

**DO**:

```promql
# Aggregate first, then calculate
sum(rate(metric[5m])) by (label)

# Use appropriate time ranges
rate(metric[5m])  # 5 minute rate
```

**DON'T**:

```promql
# Avoid instant queries for trends
metric  # ❌ Point-in-time value

# Don't use huge time ranges
rate(metric[24h])  # ❌ Too broad
```

### 3. Load Test Design

**Progressive Load Testing**:

```javascript
export const options = {
    stages: [
        { duration: "2m", target: 10 }, // Gradual ramp
        { duration: "5m", target: 50 }, // Peak load
        { duration: "2m", target: 10 }, // Ramp down
        { duration: "1m", target: 0 }, // Cool down
    ],
};
```

**Realistic User Behavior**:

```javascript
export default function () {
    // Homepage
    http.get(`${BASE_URL}/`);
    sleep(randomBetween(1, 3)); // Think time

    // Product listing
    http.get(`${BASE_URL}/products`);
    sleep(randomBetween(2, 5));

    // Product detail
    http.get(`${BASE_URL}/products/123`);
    sleep(randomBetween(3, 7));
}
```

### 4. Alerting Rules

Create `prometheus-rules.yml`:

```yaml
groups:
    - name: laravel_alerts
      rules:
          - alert: HighErrorRate
            expr: |
                sum(rate(laravel_http_requests_total{status=~"5.."}[5m])) 
                / sum(rate(laravel_http_requests_total[5m])) > 0.05
            for: 5m
            labels:
                severity: warning
            annotations:
                summary: "High error rate detected"
                description: "Error rate is {{ $value | humanizePercentage }}"

          - alert: HighLatency
            expr: |
                histogram_quantile(0.95, 
                    rate(laravel_http_response_time_seconds_bucket[5m])
                ) > 2
            for: 10m
            labels:
                severity: warning
            annotations:
                summary: "High P95 latency"
                description: "P95 latency is {{ $value }}s"

          - alert: ServiceDown
            expr: up{job="pushgateway"} == 0
            for: 1m
            labels:
                severity: critical
            annotations:
                summary: "PushGateway is down"
```

### 5. Data Retention

**Prometheus**:

```yaml
# Keep 30 days, max 10GB
command:
    - "--storage.tsdb.retention.time=30d"
    - "--storage.tsdb.retention.size=10GB"
```

**InfluxDB**:

```sql
-- Create retention policy
CREATE RETENTION POLICY "30_days" ON "k6" DURATION 30d REPLICATION 1 DEFAULT
```

### 6. Security Considerations

**Grafana**:

```yaml
environment:
    - GF_SECURITY_ADMIN_PASSWORD=${GRAFANA_PASSWORD} # Use env variable
    - GF_AUTH_ANONYMOUS_ENABLED=false
    - GF_AUTH_DISABLE_LOGIN_FORM=false
```

**Prometheus**:

```yaml
# Add basic auth if exposing publicly
# Use nginx reverse proxy with authentication
```

**PushGateway**:

```php
// In production, use authentication
$pushGateway = new PushGateway(
    'https://pushgateway.example.com',
    ['auth' => ['username', 'password']]
);
```

---

## Quick Reference

### Service URLs

| Service     | From Host Browser     | From Docker Container            | Port |
| ----------- | --------------------- | -------------------------------- | ---- |
| Laravel     | http://127.0.0.1:8000 | http://host.docker.internal:8000 | 8000 |
| Prometheus  | http://localhost:9090 | http://prometheus:9090           | 9090 |
| Grafana     | http://localhost:3000 | http://grafana:3000              | 3000 |
| PushGateway | http://localhost:9091 | http://pushgateway:9091          | 9091 |
| InfluxDB    | localhost:8086        | influxdb:8086                    | 8086 |
| Chronograf  | http://localhost:8888 | http://chronograf:8888           | 8888 |
| Redis       | localhost:6379        | redis:6379                       | 6379 |

### Common Commands

```powershell
# Start monitoring stack
docker-compose up -d

# Start Laravel
php artisan serve --port=8000

# Run smoke test
docker-compose run --rm k6 run /scripts/smoke.js

# Run stress test
docker-compose run --rm k6 run /scripts/stress.js

# View container logs
docker-compose logs -f prometheus
docker-compose logs -f grafana
docker-compose logs -f k6

# Restart specific service
docker-compose restart prometheus

# Stop everything
docker-compose down

# Stop and remove volumes (clean slate)
docker-compose down -v

# Check container status
docker ps

# Access container shell
docker-compose exec prometheus sh
docker-compose exec grafana sh

# Export Prometheus data
curl "http://localhost:9090/api/v1/query?query=laravel_http_requests_total"
```

### Essential PromQL Queries

```promql
# REQUEST METRICS
sum(rate(laravel_http_requests_total[5m]))
sum(rate(laravel_http_requests_total[5m])) by (status)
sum(rate(laravel_http_requests_total[5m])) by (endpoint)
sum(rate(laravel_http_requests_total[5m])) by (method)

# RESPONSE TIME
histogram_quantile(0.50, rate(laravel_http_response_time_seconds_bucket[5m]))
histogram_quantile(0.95, rate(laravel_http_response_time_seconds_bucket[5m]))
histogram_quantile(0.99, rate(laravel_http_response_time_seconds_bucket[5m]))

# ERROR TRACKING
sum(rate(laravel_http_requests_total{status="404"}[5m]))
sum(rate(laravel_http_requests_total{status=~"5.."}[5m]))
sum(rate(laravel_http_requests_total{status=~"4..|5.."}[5m])) /
sum(rate(laravel_http_requests_total[5m]))

# K6 METRICS
k6_vus
rate(k6_http_reqs[1m])
histogram_quantile(0.95, rate(k6_http_req_duration_bucket[1m]))
sum(rate(k6_http_req_failed[1m])) / sum(rate(k6_http_reqs[1m]))

# SYSTEM HEALTH
laravel_memory_usage_bytes
up{job="pushgateway"}
```

---

## Summary

### What You Have Now

✅ **Complete Monitoring Stack**

-   Prometheus (metrics database)
-   Grafana (visualization)
-   PushGateway (metric aggregation)
-   InfluxDB (load test history)

✅ **Application Monitoring**

-   Real-time HTTP request tracking
-   Response time distribution
-   Error rate monitoring
-   Memory usage tracking

✅ **Load Testing Infrastructure**

-   K6 framework integrated
-   Dual metrics export (InfluxDB + Prometheus)
-   Real-time test monitoring
-   Historical test analysis

✅ **Unified Dashboards**

-   Laravel application metrics
-   K6 load test metrics
-   Correlated analysis view

✅ **Production-Ready**

-   30-day metric retention
-   Alert rules configured
-   Automated provisioning
-   Docker-based deployment

### Integration Benefits

1. **During Development**:

    - Monitor real-time performance
    - Identify slow endpoints
    - Track memory usage
    - Detect errors immediately

2. **During Load Testing**:

    - See how app behaves under load
    - Correlate K6 load with Laravel metrics
    - Identify breaking points
    - Validate SLA compliance

3. **Post-Deployment**:
    - Compare before/after metrics
    - Regression testing
    - Baseline establishment
    - Continuous monitoring

### Next Steps

1. **Create Custom Dashboards** for your specific needs
2. **Set Up Alerting** via Prometheus AlertManager
3. **Automate Testing** with CI/CD integration
4. **Document Baselines** for each critical endpoint
5. **Regular Load Tests** to catch performance regressions

---

**Your monitoring and load testing infrastructure is complete!** 🎉📊

All metrics flow correctly:

-   **Laravel → PushGateway → Prometheus → Grafana** (Application Monitoring)
-   **K6 → InfluxDB + Prometheus → Grafana** (Load Testing)
-   **Unified View** in Grafana for complete analysis

**Happy Monitoring and Testing!**
