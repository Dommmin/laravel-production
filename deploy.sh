#!/bin/bash

set -e

# Create necessary directories
mkdir -p /home/deployer/laravel
cd /home/deployer/laravel

# Create docker-compose.production.yml if it doesn't exist
if [ ! -f docker-compose.production.yml ]; then
    cat > docker-compose.production.yml << 'EOL'
version: '3.8'

services:
  app:
    image: ${REGISTRY}/${IMAGE_NAME}:${TAG:-latest}
    container_name: ${COMPOSE_PROJECT_NAME}_app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./storage:/var/www/storage
      - ./docker/php/php.ini:/usr/local/etc/php/conf.d/custom.ini
      - ./docker/php/www.conf:/usr/local/etc/php-fpm.d/www.conf
      - ./docker/supervisord.conf:/etc/supervisor/supervisord.conf
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_HOST=${DB_HOST}
      - DB_PORT=${DB_PORT}
      - DB_DATABASE=${DB_DATABASE}
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
      - REDIS_HOST=${REDIS_HOST}
      - REDIS_PORT=${REDIS_PORT}
    networks:
      - laravel-network

  nginx:
    image: nginx:alpine
    container_name: ${COMPOSE_PROJECT_NAME}_nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
      - ./docker/nginx/ssl:/etc/nginx/ssl
      - nginx_logs:/var/log/nginx
    depends_on:
      - app
    networks:
      - laravel-network

networks:
  laravel-network:
    driver: bridge

volumes:
  nginx_logs:
EOL
fi

# Create necessary directories
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set proper permissions
chown -R deployer:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Create .env file from GitHub secret
echo "$ENV_FILE" > .env

# Pull and start containers
docker-compose -f docker-compose.production.yml pull
docker-compose -f docker-compose.production.yml up -d

# Run migrations and optimize
docker-compose -f docker-compose.production.yml exec -T app php artisan migrate --force
docker-compose -f docker-compose.production.yml exec -T app php artisan optimize:clear
docker-compose -f docker-compose.production.yml exec -T app php artisan optimize
