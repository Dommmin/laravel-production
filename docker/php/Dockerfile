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

RUN mkdir -p \
    storage/framework/{cache,sessions,views} \
    storage/app/public \
    bootstrap/cache \
    storage/logs \
    /var/log/supervisor \
    /var/run/supervisor \
 && chown -R www-data:www-data \
    storage \
    bootstrap/cache \
    storage/logs \
    /var/log/supervisor \
    /var/run/supervisor \
 && chmod -R 775 \
    storage \
    bootstrap/cache \
 && chmod -R 755 \
    /var/log/supervisor \
    /var/run/supervisor

COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=node-builder /var/www/public/build public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && chown -R www-data:www-data vendor

COPY docker/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

EXPOSE 9000

USER root

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
