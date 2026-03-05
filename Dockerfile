FROM php:8.4-fpm-alpine3.23

RUN apk update && apk add \
    git \
    unzip \
    && rm -rf /var/cache/apk/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . /app

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN mkdir -p storage \
    && chmod -R 777 storage public

COPY docker/php-fpm/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php-fpm/sse.conf /usr/local/etc/php-fpm.d/sse.conf

EXPOSE 8000

CMD ["php-fpm"]