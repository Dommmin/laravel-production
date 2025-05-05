FROM node:22-alpine AS node-builder

WORKDIR /var/www/html

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

# Create appuser
RUN addgroup -g 1000 appuser && \
    adduser -D -u 1000 -G appuser appuser

# Configure PHP-FPM
RUN sed -i 's/user = www-data/user = appuser/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/group = www-data/group = appuser/g' /usr/local/etc/php-fpm.d/www.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy project files
COPY --chown=appuser:appuser . .

# Copy build files from node-builder
COPY --chown=appuser:appuser --from=node-builder /var/www/html/public/build /var/www/html/public/build

# Install production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy configuration files
COPY --chown=appuser:appuser docker/supervisord.conf /etc/supervisord.conf
COPY --chown=appuser:appuser docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY --chown=appuser:appuser docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Create required directories
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    bootstrap/cache \
    storage/logs \
    /var/log/supervisor \
    /var/run/php \
    /var/log/php-fpm

# Set permissions
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

# Switch to non-root user
USER appuser

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]
