FROM node:22-alpine AS node-builder

WORKDIR /var/www

COPY package*.json ./
RUN npm ci

COPY vite.config.ts tsconfig.json ./
COPY resources resources

RUN npm run build

FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    git bash curl libpng-dev libxml2-dev zip unzip \
    freetype-dev libjpeg-turbo-dev libwebp-dev zlib-dev libzip-dev oniguruma-dev \
    postgresql-dev supervisor autoconf g++ make

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
 && pecl install redis && docker-php-ext-enable redis

RUN addgroup -g 1000 appuser && \
    adduser -D -u 1000 -G appuser appuser

RUN sed -i 's/user = www-data/user = appuser/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/group = www-data/group = appuser/g' /usr/local/etc/php-fpm.d/www.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

COPY --chown=appuser:appuser . .

COPY --chown=appuser:appuser --from=node-builder /var/www/public/build /var/www/public/build

COPY --chown=appuser:appuser docker/supervisord.conf /etc/supervisord.conf
COPY --chown=appuser:appuser docker/php/php.ini /usr/local/etc/php/php.ini
COPY --chown=appuser:appuser docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

RUN mkdir -p \
    storage/framework/{cache,sessions,views} \
    storage/app/public \
    public/storage \
    bootstrap/cache \
    storage/logs \
    /var/log/supervisor \
    /var/run/php \
    /var/log/php-fpm \
 && chown -R appuser:appuser \
    storage \
    bootstrap/cache \
    public \
    /var/log/supervisor \
    /var/run/php \
    /var/log/php-fpm \
 && chmod -R 775 storage bootstrap/cache

RUN ln -s /var/www/storage/app/public /var/www/public/storage

USER appuser

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]
