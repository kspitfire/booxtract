FROM php:7.4-cli-alpine

RUN apk update \
    && apk add ${PHPIZE_DEPS} bash git libzip-dev zlib-dev \
    && docker-php-ext-install -j$(nproc) iconv zip \
    && apk del ${PHPIZE_DEPS} \
    && rm -frv /var/cache/apk/*

# Install composer global bin
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/bin --filename=composer \
    && rm -fv composer-setup.php

WORKDIR /app
