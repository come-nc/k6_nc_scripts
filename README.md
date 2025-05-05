# k6_nc_scripts
k6 scripts for testing a Nextcloud instance performances or compare configurations

## Usage

```sh
$ k6 run -e BASEURI=https://nextcloud.local basic.js
$ k6 run -e BASEURI=https://nextcloud.local webdav.js
```

## Scripts

### basic.js

This script runs requests against 3 endpoints from Nextcloud:
- `/version.php`, where no Nextcloud code is run at all
- `/status.php`, where Nextcloud init is run, but no session is started
- `/login.php`, where a full startup happens

### webdav.js

This script creates a small text file and deletes it through webdav using `admin` as login and password.
It does not empty the trashbin afterward.

It should be improved to support other users, auth methods, and webdav operations.
