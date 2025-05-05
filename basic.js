import http from 'k6/http'
import encoding from 'k6/encoding'
import { group, check } from 'k6'
import { uuidv4 } from 'https://jslib.k6.io/k6-utils/1.0.0/index.js'

export let options = {
  iterations: 100,
  vus: 10,
  duration: '10s',
}

export default function() {
  let response = http.request('GET', `${__ENV.BASEURI}/version.php`, undefined, { tags: { group: 'version.php' } })
  check(response, {
    'status is 201 or 204': (r) => r.status === 201 || 204
  })
  response = http.request('GET', `${__ENV.BASEURI}/status.php`, undefined, { tags: { group: 'status.php' } })
  check(response, {
    'status is 201 or 204': (r) => r.status === 201 || 204
  })
  response = http.request('GET', `${__ENV.BASEURI}/index.php`, undefined, { tags: { group: 'index.php' } })
  check(response, {
    'status is 201 or 204': (r) => r.status === 201 || 204
  })
}
