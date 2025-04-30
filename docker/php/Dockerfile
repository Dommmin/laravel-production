FROM node:22-alpine AS node-builder

WORKDIR /var/www

COPY package*.json vite.config.ts tsconfig.json ./
COPY resources resources

RUN npm ci
RUN npm run build

# Zmiana z alpine na standardowy obraz
FROM php:8.4-fpm

# Instalacja pakietów
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    zlib1g-dev \
    libzip-dev \
    libonig-dev \
    libpq-dev \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

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
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Tworzenie katalogów
RUN mkdir -p \
    /var/www/storage/framework/{cache,sessions,views} \
    /var/www/storage/app/public \
    /var/www/bootstrap/cache \
    /var/www/storage/logs \
    /var/log/supervisor \
    /var/run/php \
 && chown -R www-data:www-data \
    /var/www/storage \
    /var/www/bootstrap/cache \
    /var/www/storage/logs \
    /var/log/supervisor \
    /var/run/php \
 && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
