FROM composer/composer:master-alpine

WORKDIR /var/www/html

COPY ./composer.json .

RUN install



FROM php:7.3-fpm-alpine

ENV COMPOSER_ALLOW_SUPERUSER=1

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

WORKDIR /var/www/html
