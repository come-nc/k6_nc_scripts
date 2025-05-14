import http from 'k6/http'
import encoding from 'k6/encoding'
import { group, check } from 'k6'
import { uuidv4 } from 'https://jslib.k6.io/k6-utils/1.0.0/index.js'

export let options = {
  iterations: 20,
  vus: 10,
  duration: '10s',
}

export default function() {
  let response = http.request('GET', `${__ENV.BASEURI}/version.php`, undefined, { tags: { group: 'version.php' } })
  check(response, {
    'version.php status is >= 200 and < 300': (r) => r.status >= 200 && r.status < 300
  })
  response = http.request('GET', `${__ENV.BASEURI}/status.php`, undefined, { tags: { group: 'status.php' } })
  check(response, {
    'status.php status is >= 200 and < 300': (r) => r.status >= 200 && r.status < 300
  })
  response = http.request('GET', `${__ENV.BASEURI}/index.php`, undefined, { tags: { group: 'index.php' } })
  check(response, {
    'index.php status is >= 200 and < 300': (r) => r.status >= 200 && r.status < 300
  })

  response = http.request('GET', `${__ENV.BASEURI}/index.php/csrftoken`)
  let requestToken = response.json().token
  response = http.post(
    `${__ENV.BASEURI}/index.php/login`,
    {
      user: 'admin',
      password: 'admin',
      requesttoken: requestToken
    }, {
      headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      // Add the Origin header so that the request is not blocked by the browser.
      'Origin': `${__ENV.BASEURI}`, },
  });
  check(response, {
    'login status is >= 200 and < 300': (r) => r.status >= 200 && r.status < 300
  })

  response = http.request('GET', `${__ENV.BASEURI}/index.php/apps/files/`, undefined, { tags: { group: 'apps/files' } })
  check(response, {
    'files status is >= 200 and < 300': (r) => r.status >= 200 && r.status < 300
  })


}
