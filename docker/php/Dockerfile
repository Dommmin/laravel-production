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

RUN addgroup -g 1000 appuser && \
    adduser -D -u 1000 -G appuser appuser

# Zmień właściciela plików
RUN chown -R appuser:appuser /var/www

# Zmień konfigurację PHP-FPM
RUN sed -i 's/user = www-data/user = appuser/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/group = www-data/group = appuser/g' /usr/local/etc/php-fpm.d/www.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Kopiujemy projekt
COPY --chown=www-data:www-data . .

# Kopiujemy pliki build z etapu node-builder
COPY --chown=www-data:www-data --from=node-builder /var/www/public/build /var/www/public/build

# Instalacja produkcyjna
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
 && chown -R www-data:www-data vendor

# Kopiowanie konfiguracji Supervisor i PHP
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Tworzenie katalogów i ustawienie uprawnień
RUN mkdir -p \
    storage/framework/{cache,sessions,views} \
    storage/app/public \
    bootstrap/cache \
    storage/logs \
    /var/log/supervisor \
    /var/run/php \
    /var/log/php-fpm \
 && chown -R www-data:www-data \
    storage \
    bootstrap/cache \
    storage/logs \
    /var/log/supervisor \
    /var/run/php \
    /var/log/php-fpm \
    public \
    public/build \
 && chmod -R 775 storage bootstrap/cache public public/build

# Weryfikacja plików build
RUN ls -la /var/www/public/build

# Skopiuj pliki public do wolumenu
VOLUME ["/var/www/public"]

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]
