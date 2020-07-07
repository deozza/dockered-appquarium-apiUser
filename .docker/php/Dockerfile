FROM php:7.3-fpm as base

RUN apt-get update \
    && apt-get install -y libzip-dev zip libcurl4-openssl-dev pkg-config libssl-dev gnupg

RUN set -xe \
    && docker-php-ext-install zip

RUN pecl install apcu \
    && docker-php-ext-enable apcu

RUN pecl install mongodb

RUN  apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 68818C72E52529D4 \
    && echo "deb http://repo.mongodb.org/apt/ubuntu bionic/mongodb-org/4.0 multiverse" | tee /etc/apt/sources.list.d/mongodb-org-4.0.list \
    && apt-get update \
    && apt-get install -y mongodb-org

COPY .docker/php/ini/conf.d/memory_limit.ini /usr/local/etc/php/conf.d
COPY .docker/php/ini/conf.d/mongodb.ini /usr/local/etc/php/conf.d

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls \
    && mv composer.phar /usr/local/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER 1
RUN mkdir -p /var/www/.composer && chown -R www-data /var/www/.composer

FROM base as dev

RUN PATH=$PATH:/var/www/apiUser/vendor/bin:bin

WORKDIR /var/www/apiUser

ENV APP_ENV=dev

EXPOSE 9000

CMD ["php-fpm"]

FROM base AS test

COPY apiUser/ /var/www/apiUser

RUN PATH=$PATH:/var/www/apiUser/vendor/bin:bin

WORKDIR /var/www/apiUser

RUN composer validate \
    && composer install --prefer-dist --no-scripts --no-progress -q -o

EXPOSE 9000

CMD ["php-fpm"]

FROM base AS prod

COPY apiUser/ /var/www/apiUser

RUN PATH=$PATH:/var/www/apiUser/vendor/bin:bin

WORKDIR /var/www/apiUser

ENV APP_ENV=prod
ENV CORS_ALLOW_ORIGIN="https://apigateway.appquarium.fr"

RUN composer install --no-dev --optimize-autoloader --prefer-dist

RUN chmod -R 777 /var/www/apiUser/var/

EXPOSE 9000

CMD ["php-fpm"]