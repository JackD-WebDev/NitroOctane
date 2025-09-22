FROM php:8.4-cli-alpine

WORKDIR /var/www/laravel

COPY --from=mlocati/php-extension-installer \
    /usr/bin/install-php-extensions \
    /usr/local/bin/

RUN apk update && \
    apk upgrade && \
    apk add --no-cache \
    curl \
    wget \
    nano \
    git \
    ncdu \
    procps \
    ca-certificates \
    supervisor \
    ffmpeg \
    libsodium-dev && \
    install-php-extensions \
    bz2 \
    pcntl \
    mbstring \
    bcmath \
    sockets \
    pgsql \
    pdo_pgsql \
    opcache \
    exif \
    pdo_mysql \
    zip \
    intl \
    gd \
    redis \
    rdkafka \
    memcached \
    igbinary \
    ldap \
    imagick \
    xdebug \
    swoole && \
    docker-php-source delete && \
    rm -rf /var/cache/apk/* /tmp/* /var/tmp/*

RUN apk add --no-cache php-xdebug

RUN docker-php-ext-enable xdebug 

COPY --from=composer:latest \
    /usr/bin/composer \
    /usr/bin/composer

COPY php/supervisord.conf /etc/supervisord.conf

COPY api .

RUN addgroup -g 1000 -S laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel && \
    chown -R laravel:laravel /var/www/laravel

USER laravel

CMD ["/usr/bin/supervisord", "-n"]

EXPOSE 8000