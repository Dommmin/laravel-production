#!/bin/bash

set -e

# Create necessary directories
mkdir -p /home/deployer/laravel
cd /home/deployer/laravel

if [ ! -f docker-compose.yml ]; then
    echo "docker-compose.yml not found."
    exit 1
fi

# Create necessary directories
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set proper permissions
chown -R deployer:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

docker-compose pull
docker-compose -d

docker-compose exec app bash -c "php artisan migrate --force"
docker-compose exec app bash -c "php artisan optimize:clear"
docker-compose exec app bash -c "php artisan optimize"
