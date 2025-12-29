# K6 + Prometheus + Grafana Real-Time Monitoring Setup

## Overview

This guide shows how to monitor k6 load tests in **real-time** using **Prometheus** and **Grafana**. Your k6 tests will export metrics to **both InfluxDB (current)** and **Prometheus** for dual monitoring capabilities.

---

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Your Browser (Windows Host)              │
│  http://localhost:3000 (Grafana)                            │
│  http://localhost:9090 (Prometheus)                         │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                    Docker Network                           │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Prometheus   │  │   Grafana    │  │  InfluxDB    │      │
│  │  :9090       │  │   :3000      │  │   :8086      │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│         ↑                  ↓                    ↑            │
│         │           Queries both datasources   │            │
│         └───────────────────────────────────────┘            │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │                   k6 Container                       │   │
│  │  • Scrape endpoint: :6565/metrics (Prometheus)       │   │
│  │  • Push to InfluxDB: :8086                          │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│              Laravel App (Host: Windows)                    │
│  http://127.0.0.1:8000                                      │
│  Target for k6 load tests                                   │
└─────────────────────────────────────────────────────────────┘
```

---

## What's Been Configured

### 1. **Docker Compose Updates**

-   **k6**: Now exposes Prometheus metrics on port `6565` AND writes to InfluxDB
-   **Prometheus**: Scrapes k6 metrics every 15s from `k6:6565`
-   **Grafana**: Provisioned with both InfluxDB and Prometheus datasources

### 2. **Prometheus Configuration** (`prometheus.yml`)

Added k6 scrape job:

```yaml
- job_name: "k6"
  honor_labels: true
  static_configs:
      - targets: ["k6:6565"]
```

### 3. **Grafana Datasources** (`grafana-datasource.yaml`)

Both datasources auto-provisioned:

-   **influxdb**: For existing k6 InfluxDB dashboard
-   **Prometheus**: For new real-time k6 Prometheus dashboard

### 4. **New Dashboard**

Created: `dashboards/k6-prometheus.json`

-   Folder: "K6 Load Testing"
-   Shows: VUs, P50/P90/P95/P99 response times, error rate, request rate
-   Auto-refreshes every 5 seconds

---

## How to Run & Monitor

### Step 1: Restart the Stack

```powershell
# Restart to apply Prometheus scrape config
docker-compose restart prometheus grafana

# Or full restart
docker-compose down
docker-compose up -d
```

### Step 2: Verify Prometheus is Scraping k6

Open http://localhost:9090 → **Status** → **Targets**

You should see:

-   ✅ **k6** (k6:6565) - State: UP
-   ✅ **pushgateway** (pushgateway:9091) - State: UP
-   ✅ **R&S Marketplace** (host.docker.internal:8000) - State: UP/DOWN

### Step 3: Start Laravel App (Target)

```powershell
# In terminal 1
php artisan serve --port=8000
```

### Step 4: Run k6 Load Test

```powershell
# In terminal 2
docker-compose run --rm k6 run /scripts/smoke.js
```

k6 will now:

-   ✅ Write metrics to InfluxDB (original behavior)
-   ✅ Expose Prometheus metrics on :6565
-   ✅ Prometheus scrapes them every 15s

### Step 5: Open Grafana Dashboards

Go to http://localhost:3000 (admin/admin)

**Two k6 dashboards available:**

1. **K6 Dashboard (InfluxDB)** - Legacy dashboard

    - Path: Dashboards → K6 Load Testing → k6
    - Datasource: influxdb
    - Best for: Post-test analysis, detailed metrics

2. **K6 Load Test (Prometheus)** - NEW real-time dashboard
    - Path: Dashboards → K6 Load Testing → K6 Load Test (Prometheus)
    - Datasource: Prometheus
    - Best for: **Real-time monitoring during test execution**

---

## Real-Time Monitoring Workflow

### During Test Execution

**Open both dashboards side-by-side:**

1. **Prometheus Dashboard** (k6-prometheus.json)

    - Set refresh: **5 seconds** (top-right)
    - Time range: **Last 15 minutes**
    - Watch metrics update **in real-time** as test runs

2. **Key Metrics to Watch:**

| Metric                | What It Shows           | Good               | Bad            |
| --------------------- | ----------------------- | ------------------ | -------------- |
| **Virtual Users**     | Current load            | Ramps smoothly     | Stuck/jumps    |
| **P95 Response Time** | 95th percentile latency | < 1000ms           | > 2000ms       |
| **Error Rate**        | % failed requests       | 0%                 | > 10%          |
| **Request Rate**      | Requests/sec            | Increases with VUs | Plateaus early |

3. **Response Time Percentiles Graph**

    - P50, P90, P95, P99 lines
    - **Flat lines** = app handles load well
    - **Spikes** = bottleneck detected

4. **Threshold Violations**
    - k6 console shows threshold failures
    - Example: `✗ 'p(95)<2000'` = P95 exceeded 2000ms

---

## Understanding the Metrics

### Prometheus Queries Used

**Virtual Users:**

```promql
k6_vus
```

**P95 Response Time:**

```promql
histogram_quantile(0.95, sum(rate(k6_http_req_duration_bucket[1m])) by (le))
```

**Error Rate:**

```promql
sum(rate(k6_http_req_failed[1m])) / sum(rate(k6_http_reqs[1m]))
```

**Request Rate:**

```promql
sum(rate(k6_http_reqs[1m]))
```

### Available k6 Metrics in Prometheus

| Metric                   | Type      | Description                   |
| ------------------------ | --------- | ----------------------------- |
| `k6_vus`                 | Gauge     | Current virtual users         |
| `k6_vus_max`             | Gauge     | Max VUs configured            |
| `k6_http_reqs`           | Counter   | Total HTTP requests           |
| `k6_http_req_duration`   | Histogram | Request duration distribution |
| `k6_http_req_failed`     | Counter   | Failed requests               |
| `k6_http_req_blocked`    | Summary   | Time blocked before request   |
| `k6_http_req_connecting` | Summary   | Connection time               |
| `k6_http_req_sending`    | Summary   | Time sending data             |
| `k6_http_req_waiting`    | Summary   | Time waiting for response     |
| `k6_http_req_receiving`  | Summary   | Time receiving response       |
| `k6_iterations`          | Counter   | Total iterations completed    |
| `k6_iteration_duration`  | Histogram | Iteration duration            |
| `k6_checks`              | Counter   | Check results (passed/failed) |

---

## Best Practices

### 1. **Metric Retention**

Prometheus stores metrics for **15 days** by default.

To change retention:

```yaml
# In docker-compose.yml, add to prometheus command:
command:
    - "--config.file=/etc/prometheus/prometheus.yml"
    - "--storage.tsdb.retention.time=30d" # Keep for 30 days
```

### 2. **Label Usage**

Add custom labels to k6 metrics:

```javascript
// In smoke.js
export let options = {
    tags: {
        environment: "staging",
        test_type: "smoke",
        app: "R&S-Marketplace",
    },
};
```

Query in Prometheus:

```promql
k6_http_req_duration{environment="staging"}
```

### 3. **Multiple Test Scenarios**

Create separate test files:

```
scripts/
  smoke.js      # Light load (5 VUs, 2min)
  stress.js     # Heavy load (100 VUs, 10min)
  spike.js      # Sudden spike (0→200 VUs in 30s)
  soak.js       # Endurance (50 VUs, 2 hours)
```

Run with labels:

```powershell
docker-compose run --rm k6 run --tag testid=smoke-001 /scripts/smoke.js
```

### 4. **Alerting on Thresholds**

Add Prometheus alerting rules:

```yaml
# prometheus-rules.yml
groups:
    - name: k6_alerts
      rules:
          - alert: HighErrorRate
            expr: sum(rate(k6_http_req_failed[1m])) / sum(rate(k6_http_reqs[1m])) > 0.05
            for: 1m
            annotations:
                summary: "k6 error rate > 5%"

          - alert: HighLatency
            expr: histogram_quantile(0.95, sum(rate(k6_http_req_duration_bucket[1m])) by (le)) > 2000
            for: 2m
            annotations:
                summary: "k6 P95 latency > 2s"
```

---

## Comparing InfluxDB vs Prometheus Dashboards

| Feature              | InfluxDB Dashboard     | Prometheus Dashboard          |
| -------------------- | ---------------------- | ----------------------------- |
| **Refresh Rate**     | Manual/slow            | 5s auto-refresh               |
| **Query Language**   | InfluxQL               | PromQL                        |
| **Real-time**        | Delayed (post-write)   | Scrape-based (near real-time) |
| **Retention**        | Until manually deleted | 15 days default               |
| **Best For**         | Historical analysis    | Live monitoring               |
| **Plugins Required** | blackmirror1 (Angular) | None (native Grafana)         |

**Recommendation:** Use **Prometheus dashboard during tests**, **InfluxDB for post-test deep dive**.

---

## Troubleshooting

### Problem: Prometheus shows "No data"

**Check k6 is exposing metrics:**

```powershell
# While k6 test is running
curl http://localhost:6565/metrics
```

Should return Prometheus-format metrics:

```
# HELP k6_vus Current number of active virtual users
# TYPE k6_vus gauge
k6_vus 50
```

### Problem: Prometheus Target is DOWN

**Check Prometheus targets:**

```
http://localhost:9090/targets
```

If `k6` target is DOWN:

1. Ensure k6 container is running: `docker ps | grep k6`
2. Check network: `docker-compose exec prometheus ping k6`
3. Restart Prometheus: `docker-compose restart prometheus`

### Problem: Grafana shows "Datasource not found"

**Re-provision datasources:**

```powershell
docker-compose restart grafana
# Wait 15 seconds for startup
Start-Sleep -Seconds 15
```

Check datasources: http://localhost:3000/datasources

Should show:

-   ✅ influxdb (default)
-   ✅ Prometheus

---

## Advanced: Multi-Target Load Testing

Test multiple endpoints simultaneously:

```javascript
// scripts/multi-endpoint.js
import http from "k6/http";
import { check, group, sleep } from "k6";

export let options = {
    stages: [
        { duration: "2m", target: 30 },
        { duration: "3m", target: 30 },
        { duration: "1m", target: 0 },
    ],
};

export default function () {
    group("Homepage", function () {
        let res = http.get("http://host.docker.internal:8000");
        check(res, { "homepage status 200": (r) => r.status === 200 });
    });

    group("Products API", function () {
        let res = http.get("http://host.docker.internal:8000/api/products");
        check(res, { "products status 200": (r) => r.status === 200 });
    });

    group("Cart API", function () {
        let res = http.get("http://host.docker.internal:8000/api/cart");
        check(res, { "cart status 200": (r) => r.status === 200 });
    });

    sleep(1);
}
```

Prometheus will automatically tag metrics by `group` label. Filter in Grafana:

```promql
k6_http_req_duration{group="Products API"}
```

---

## Quick Reference

### Start Monitoring Stack

```powershell
docker-compose up -d influxdb prometheus grafana
```

### Run k6 Test with Live Monitoring

```powershell
# Terminal 1: Laravel app
php artisan serve --port=8000

# Terminal 2: k6 test
docker-compose run --rm k6 run /scripts/smoke.js

# Browser: Open Grafana
http://localhost:3000 → K6 Load Test (Prometheus)
```

### View Raw Metrics

-   **Prometheus**: http://localhost:9090/graph
-   **k6 metrics**: http://localhost:6565/metrics (during test)
-   **InfluxDB**: http://localhost:8888 (Chronograf)

### Stop Everything

```powershell
docker-compose down
```

---

## Next Steps

1. **Create baseline tests** for each critical endpoint
2. **Run daily smoke tests** to catch regressions early
3. **Set up alerting** in Prometheus for threshold violations
4. **Compare before/after** deployments using historical Prometheus data
5. **Correlate k6 metrics** with Laravel app metrics (PushGateway) in the same Grafana dashboard

---

## Summary

You now have a **dual-monitoring** setup:

✅ **InfluxDB**: Detailed post-test analysis  
✅ **Prometheus**: Real-time monitoring during tests  
✅ **Grafana**: Unified view of both datasources  
✅ **Persistent metrics**: 15-day retention in Prometheus  
✅ **Auto-provisioned dashboards**: No manual setup needed

**Your monitoring stack is production-ready!** 🎉
