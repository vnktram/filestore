## Filestore

RESTful API for hassle free file upload

Filestore is a cloud-enabled, image storage.

  * Accepts URLs, CSV with URLs and physical image data
  * Returns JSON with URL

FileStore is built to act as a on-demand file-serve service.

### Version
1.0

### Tech

Built on PHP and JS:

* [Twitter Bootstrap] - great UI boilerplate for modern web apps
* [jQuery] - needs not introduction

### Installation

Filestore can be used via RESTful API calls.

You need to POST data to the following address:

```sh
POST | filestore-fest.rhcloud.com/push
```

Parameters(Any one):
```sh
CSV : physical csv file which contains URLs.
URL : a single image URL
IMAGE : physical image file
```

### Todos

 - Accept multiple URLs via URL parameter
 - Switch to lossless compression
 - Add authentication to restrict uploads and serves
 
### Repo
 You can find the source at [git]
 You can clone it via [git-repo-url]
   [git]: <https://github.com/Venkat5694/filestore>
   [git-repo-url]: <https://github.com/Venkat5694/filestore.git>
   [Twitter Bootstrap]: <http://twitter.github.com/bootstrap/>
   [jQuery]: <http://jquery.com>
 
