FROM node:22-alpine AS node-builder

WORKDIR /var/www

COPY package*.json vite.config.ts tsconfig.json ./
COPY resources resources

RUN npm ci
RUN npm run build

FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    git bash curl libpng-dev libxml2-dev zip unzip \
    freetype-dev libjpeg-turbo-dev libwebp-dev zlib-dev libzip-dev oniguruma-dev \
    postgresql-dev supervisor

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Kopiujemy projekt
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=node-builder /var/www/public/build public/build

# Instalacja produkcyjna
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
 && chown -R www-data:www-data vendor

# Kopiowanie konfiguracji Supervisor i PHP
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

RUN mkdir -p \
    /var/run/php \
    /var/log/php-fpm \
 && chown -R www-data:www-data \
    /var/run/php \
    /var/log/php-fpm \
 && chmod -R 755 /var/run/php /var/log/php-fpm

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]
