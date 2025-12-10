FROM php:8.4-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql
RUN curl -sS https://getcomposer.org/installer | php -- \
        --install-dir=/usr/local/bin --filename=composer

COPY --link \
    --from=ghcr.io/symfony-cli/symfony-cli:latest \
    /usr/local/bin/symfony /usr/local/bin/symfony

RUN apk update;
RUN apk add git;

WORKDIR /app
COPY . .
RUN composer install

#CMD symfony server:start
