FROM composer:latest

WORKDIR /var/www/laravel

RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel
 
USER laravel

ENTRYPOINT [ "composer" ]