import http from 'k6/http'
import encoding from 'k6/encoding'
import { group, check } from 'k6'
import { uuidv4 } from 'https://jslib.k6.io/k6-utils/1.0.0/index.js'

export let options = {
  iterations: 20,
  vus: 10,
  duration: '10s',
}

const createFile = url => {
  const body = 'some content'
  const headers = {
    'Authorization': 'Basic ' + encoding.b64encode('admin:admin'),
    'Content-Type': 'application/x-www-form-urlencoded'
  }

  const response = http.request('PUT', url, body, { headers: headers, tags: { group: 'createFile' } })
  check(response, {
    'status is 201 or 204': (r) => r.status === 201 || 204
  })
}

const deleteFile = (url) => {
  const headers = {
    'Authorization': 'Basic ' + encoding.b64encode('admin:admin')
  }
  const response = http.request('DELETE', url, undefined, { headers: headers, tags: { group: 'deleteFile' } })
  check(response, {
    'status is 204': (r) => r.status === 204
  })
}

export default function() {
  group('webdav', function () {
    const fileName = `${uuidv4()}.txt`
    const url = `${__ENV.BASEURI}/remote.php/webdav/${fileName}`
    createFile(url)
    deleteFile(url)
  });
}
