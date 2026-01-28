import { check, sleep } from 'k6';
import http from 'k6/http';

export let options = {
  stages: [
    { duration: '30s', target: 200 },   // Start with 10 VUs
    { duration: '30s', target: 300 },    // Hold at 10
    { duration: '30s', target: 0 },    // Ramp down
  ],
  thresholds: {
    http_req_duration: ['p(95)<2000'],
    http_req_failed: ['rate<0.05'],
  },
};

export default function () {
  const res = http.get('http://host.docker.internal:8000/'); // Laravel app
  check(res, { 'status is 200': (r) => r.status === 200 });
  sleep(1);
}
