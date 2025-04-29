# Stage 1: Build frontend assets with Vite
FROM node:22-alpine AS node-builder
WORKDIR /var/www

# Copy Node files
COPY package*.json vite.config.ts tsconfig.json ./
# Copy source
COPY resources resources

# Install and build
RUN npm ci
RUN npm run build

# Stage 2: Build PHP application
FROM php:8.4-fpm-alpine

# Install PHP and system dependencies
RUN apk add --no-cache \
    git bash curl libpng-dev libxml2-dev zip unzip \
    freetype-dev libjpeg-turbo-dev libwebp-dev zlib-dev libzip-dev oniguruma-dev \
    postgresql-dev supervisor

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Prepare directories
WORKDIR /var/www

RUN mkdir -p /var/log/supervisor \
    && mkdir -p /var/run/supervisor \
    && chown -R www-data:www-data /var/log/supervisor /var/run/supervisor

# Copy application source
COPY . .

# Copy built assets from node-builder (Vite default output folder: public/build)
COPY --from=node-builder /var/www/public/build public/build

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy Supervisor config
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf

# Expose FPM port
EXPOSE 9000

# Start supervisord
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
