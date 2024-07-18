ARG PHP_VERSION=8.3

FROM php:${PHP_VERSION}-alpine

RUN apk update && apk add --no-cache $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install sockets

COPY .docker/php/kariricode-php.ini /usr/local/etc/php/conf.d/

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apk del --purge $PHPIZE_DEPS && rm -rf /var/cache/apk/*

# Keep the container running without doing anything
CMD tail -f /dev/null
