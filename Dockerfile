# syntax=docker/dockerfile:1
# Dockerfile.prod — production image only. No bind mount, no dev tooling.

##############################################################################
# base: system deps + PHP extensions
##############################################################################
FROM php:8.4-apache AS base

ENV APP_HOME=/var/www/html
ENV USERNAME=www-data
ENV TZ='America/Toronto'

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip zip curl \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev libicu-dev \
    libpq-dev postgresql-client \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip intl opcache

RUN a2enmod rewrite headers

ENV APACHE_DOCUMENT_ROOT=${APP_HOME}/public
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN printf '<Directory %s>\n\tOptions Indexes FollowSymLinks\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n' "${APACHE_DOCUMENT_ROOT}" \
    > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
WORKDIR $APP_HOME

##############################################################################
# frontend-build: compile Vite/Vue assets
##############################################################################
FROM node:20-slim AS frontend-build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

##############################################################################
# final production image
##############################################################################
FROM base AS production

COPY . .
COPY --from=frontend-build /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && chown -R www-data:www-data $APP_HOME \
    && chmod -R 775 storage bootstrap/cache

COPY docker/production-entrypoint.sh /usr/local/bin/production-entrypoint.sh
RUN chmod +x /usr/local/bin/production-entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["production-entrypoint.sh"]
