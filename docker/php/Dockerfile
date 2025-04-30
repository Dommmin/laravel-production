# Stage 1: Build frontend assets with Vite
FROM node:22-alpine AS node-builder
WORKDIR /var/www

# Copy package manifests and Vite config
COPY package*.json vite.config.ts tsconfig.json ./
# Copy frontend sources
COPY resources resources

# Install & build
RUN npm ci
RUN npm run build

# Stage 2: Build PHP application
FROM php:8.4-fpm-alpine

# Install system dependencies & Supervisor
RUN apk add --no-cache \
    git bash curl libpng-dev libxml2-dev zip unzip \
    freetype-dev libjpeg-turbo-dev libwebp-dev zlib-dev libzip-dev oniguruma-dev \
    postgresql-dev supervisor

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Copy Composer binary
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Prepare working dir
WORKDIR /var/www

# Create & set permissions on storage, cache & logs (host volume mounts will overlay)
RUN mkdir -p storage/framework/{cache,sessions,views} \
    && mkdir -p storage/app/public \
    && mkdir -p bootstrap/cache \
    && mkdir -p storage/logs \
    && mkdir -p /var/log/supervisor \
    && chown -R www-data:www-data storage bootstrap/cache storage/logs \
    && chown -R www-data:www-data /var/log/supervisor \
    && chmod -R 755 /var/log/supervisor \
    && chmod -R 775 storage bootstrap/cache storage/logs

# Copy application code & Vite build output
COPY . .
COPY --from=node-builder /var/www/public/build public/build

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && chown -R www-data:www-data vendor

# Copy Supervisor config
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Expose FPM port
EXPOSE 9000

# Launch Supervisord (starts php-fpm, Horizon, workers, etc.)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
