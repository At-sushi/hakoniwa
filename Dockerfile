FROM php:7.3-fpm-alpine

WORKDIR /var/www/html
COPY ./composer.json .

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apk upgrade --update && \
	apk --no-cache add \
		git \
		curl-dev \
		libpng-dev \
		libjpeg-turbo-dev \
		libzip-dev zip unzip\
		# xdebug
		autoconf make g++ gcc

RUN docker-php-ext-configure zip --with-libzip=/usr/include && \
	docker-php-ext-install \
		-j$(nproc) \
		zip \
		opcache \
	&& \
	pecl install xdebug && \
	docker-php-ext-enable xdebug

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install

WORKDIR /var/www/html
