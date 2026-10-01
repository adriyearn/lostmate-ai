# syntax=docker/dockerfile:1

# --- Stage 1: build frontend assets (Bootstrap CSS/JS via Vite) ---
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY resources resources
COPY vite.config.js ./
RUN npm run build

# --- Stage 2: PHP application ---
FROM php:8.3-apache

# Only the PHP extensions Laravel + this app actually use. No GD/exif -
# nothing here resizes or inspects images, it just stores the uploaded file.
RUN apt-get update && apt-get install -y \
        libzip-dev zip unzip libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring bcmath pcntl zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction

# Point Apache at Laravel's public/ directory, not the project root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && printf '<Directory /var/www/html/public>\n\tAllowOverride All\n</Directory>\n' >> /etc/apache2/apache2.conf

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]
