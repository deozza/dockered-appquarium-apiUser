FROM php:7.3-fpm

RUN apt-get update \
    && apt-get install -y libzip-dev zip libcurl4-openssl-dev pkg-config libssl-dev gnupg

RUN set -xe \
    && docker-php-ext-install zip

RUN pecl install apcu \
    && docker-php-ext-enable apcu

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN pecl install mongodb

RUN  apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 68818C72E52529D4 \
    && echo "deb http://repo.mongodb.org/apt/ubuntu bionic/mongodb-org/4.0 multiverse" | tee /etc/apt/sources.list.d/mongodb-org-4.0.list \
    && apt-get update \
    && apt-get install -y mongodb-org

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls \
    && mv composer.phar /usr/local/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER 1
RUN mkdir -p /var/www/.composer && chown -R www-data /var/www/.composer

RUN PATH=$PATH:/var/www/apiUser/vendor/bin:bin

WORKDIR /var/www/apiUser

CMD ["php-fpm"]