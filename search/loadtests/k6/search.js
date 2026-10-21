import http from 'k6/http'
import { check, sleep } from 'k6'

const baseURL = __ENV.SEARCH_BASE_URL || 'http://127.0.0.1:8080'

export const options = {
  scenarios: {
    search_read: {
      executor: 'ramping-vus',
      startVUs: 0,
      stages: [
        { duration: '30s', target: 20 },
        { duration: '1m', target: 20 },
        { duration: '15s', target: 0 },
      ],
      gracefulRampDown: '10s',
    },
  },
  thresholds: {
    http_req_failed: ['rate<0.01'],
    http_req_duration: ['p(95)<800', 'p(99)<1500'],
  },
}

const queries = [
  { q: 'sofa', store_id: '10' },
  { q: 'chair', store_id: '10', sort: 'price_asc' },
  { q: 'lamp', store_id: '11', available: 'true' },
  { q: 'desk', store_id: '10', page: '2' },
]

export default function () {
  const sample = queries[Math.floor(Math.random() * queries.length)]
  const params = new URLSearchParams(sample)
  const res = http.get(`${baseURL}/search?${params.toString()}`)

  check(res, {
    'status is 200': (r) => r.status === 200,
    'has data envelope': (r) => r.json('data') !== undefined,
  })

  sleep(0.2)
}
