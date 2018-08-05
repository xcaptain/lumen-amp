# lumen amphp

using amphp http server to serve lumen app

## Goal

Replace the original php-fpm way to serve lumen project, using go/nodejs like way to serve lumen app using [https://github.com/amphp/http-server](https://github.com/amphp/http-server)

## How to test

1. clone the demo project
2. `composer install` install dependencies
3. `./vendor/bin/phpunit` run unit tests
4. `php public/index.php` to interact with the app in [http://localhost:8000](http://localhost:8000)

## TODO

- [ ] upload file still not work
- [ ] test middlewares, especially terminate middleware
- [ ] add benchmark with php-fpm way