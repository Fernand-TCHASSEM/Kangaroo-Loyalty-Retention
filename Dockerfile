# syntax=docker/dockerfile:1

##############################################################################
# base: shared system deps + PHP extensions, used by both dev and production.
##############################################################################
FROM php:8.4-apache AS base

ENV APP_HOME=/var/www/html
ENV USERNAME=www-data
ENV TZ='America/Toronto'

# ---- System dependencies ----
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    curl \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    postgresql-client \
    && rm -rf /var/lib/apt/lists/*

# ---- PHP extensions Laravel typically needs ----
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# ---- Apache config ----
RUN a2enmod rewrite headers

# Point Apache's document root at Laravel's public/ directory
ENV APACHE_DOCUMENT_ROOT=${APP_HOME}/public
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow .htaccess overrides (needed for Laravel's front-controller routing)
RUN printf '<Directory %s>\n\tOptions Indexes FollowSymLinks\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n' "${APACHE_DOCUMENT_ROOT}" \
    > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# ---- Composer ----
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

WORKDIR $APP_HOME

##############################################################################
# frontend-build: compile Vite/Vue assets. Only reachable from `production`;
# its Node toolchain never ends up in the final production image.
##############################################################################
FROM node:20-slim AS frontend-build

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

##############################################################################
# production: standalone image, no bind mount. Build with
#   docker build --target production -t loyalty-retention .
# Runs as root so the entrypoint can rewrite Apache's port at boot; Apache
# itself still serves requests as www-data via its own User/Group directives.
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

##############################################################################
# dev: local docker-compose target (bind-mounted source, Vite dev server).
# Left as the last stage so `docker build .` with no --target still produces
# this image, matching the existing local setup.
##############################################################################
FROM base AS dev

ARG HOST_UID=1000
ARG HOST_GID=1000

# ---- Node.js (for Vite / npm run dev, required with Inertia + Vue) ----
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Fix permissions for www-data user and change owner to www-data
RUN mkdir -p /home/$USERNAME && chown $USERNAME:$USERNAME /home/$USERNAME \
  && usermod -o -u $HOST_UID $USERNAME -d /home/$USERNAME \
  && groupmod -o -g $HOST_GID $USERNAME \
  && chown -R ${USERNAME}:${USERNAME} $APP_HOME \
  && chmod -R 755 $APP_HOME

USER ${USERNAME}

CMD ["apache2-foreground"]
