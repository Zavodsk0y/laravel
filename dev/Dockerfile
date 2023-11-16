FROM php:8.1-fpm-alpine

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

COPY ../dev/php.ini $PHP_INI_DIR/php.ini

RUN set -ex \
  && apk --no-cache add \
    postgresql-dev

RUN docker-php-ext-install pdo pdo_pgsql

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS
RUN apk add --update linux-headers
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
RUN apk del -f .build-deps

RUN apk add --no-cache npm

WORKDIR /app/cloud

COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

