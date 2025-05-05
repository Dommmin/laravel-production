FROM node:22-alpine AS node-builder

WORKDIR /var/www

COPY package*.json vite.config.ts tsconfig.json ./
COPY resources resources

RUN npm ci
RUN npm run build

FROM php:8.4-fpm-alpine AS php-stage

# Instalacja zależności systemowych + Redis
RUN apk add --no-cache \
    git bash curl libpng-dev libxml2-dev zip unzip \
    freetype-dev libjpeg-turbo-dev libwebp-dev zlib-dev libzip-dev oniguruma-dev \
    postgresql-dev supervisor autoconf g++ make

# PHP: instalacja rozszerzeń
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# PHP: instalacja i aktywacja rozszerzenia redis
RUN pecl install redis && docker-php-ext-enable redis

# Tworzenie użytkownika aplikacyjnego
RUN addgroup -g 1000 appuser && \
    adduser -D -u 1000 -G appuser appuser

# PHP-FPM pod appuser
RUN sed -i 's/user = www-data/user = appuser/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/group = www-data/group = appuser/g' /usr/local/etc/php-fpm.d/www.conf

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Kopiowanie kodu źródłowego
COPY --chown=appuser:appuser . .

# Kopiowanie wygenerowanego frontu (vite)
COPY --chown=appuser:appuser --from=node-builder /var/www/public/build /var/www/public/build

# Instalacja zależności PHP (production-only)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Konfiguracje niestandardowe
COPY --chown=appuser:appuser docker/supervisord.conf /etc/supervisord.conf
COPY --chown=appuser:appuser docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY --chown=appuser:appuser docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Tworzenie katalogów
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    public/storage \
    bootstrap/cache \
    storage/logs \
    /var/log/supervisor \
    /var/run/php \
    /var/log/php-fpm

# Ustawienia uprawnień
RUN chown -R appuser:appuser \
    storage \
    bootstrap/cache \
    storage/logs \
    /var/log/supervisor \
    /var/run/php \
    /var/log/php-fpm \
    public \
    public/build \
 && chmod -R 775 storage bootstrap/cache public public/build

USER appuser

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]
