FROM php:8.4-fpm-alpine

# Install dependencies
RUN apk add --no-cache \
    git curl libpng-dev libxml2-dev zip unzip \
    nodejs npm freetype-dev libjpeg-turbo-dev \
    libwebp-dev zlib-dev libzip-dev oniguruma-dev \
    postgresql-dev supervisor

# Configure PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Ensure PHP-FPM directory exists
RUN mkdir -p /usr/local/etc/php-fpm.d

WORKDIR /var/www

# Create storage structure first
RUN mkdir -p storage/framework/{cache,sessions,testing,views} \
    && mkdir -p storage/app/public \
    && mkdir -p storage/logs

# Copy application files
COPY . .

# Set permissions (now the directories exist)
RUN chown -R www-data:www-data /var/www/storage \
    && chmod -R 775 /var/www/storage

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
