import { check, sleep } from 'k6';
import http from 'k6/http';

export let options = {
  stages: [
    { duration: '2m', target: 50 },   // ramp to 50 VUs
    { duration: '5m', target: 50 },   // hold 50 VUs
    { duration: '2m', target: 0 },
  ],
  thresholds: {
    http_req_duration: ['p(95)<2000'],
    http_req_failed: ['rate<0.2'],
  },
  // Enable Prometheus remote write
  ext: {
    loadimpact: {
      distribution: {
        'k6-load-test': { loadZone: 'amazon:us:ashburn', percent: 100 }
      }
    }
  }
};

export default function () {
  const res = http.get('http://host.docker.internal:8000'); // Laravel app
  check(res, { 'status is 200': (r) => r.status === 200 });
  sleep(1);
}
