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

# Create .env file from GitHub secret
echo "$ENV_FILE" > .env

# Pull and start containers
docker-compose -f docker compose.yml pull
docker-compose -f docker compose.yml up -d

# Run migrations and optimize
docker-compose -f docker compose.yml exec -T app php artisan migrate --force
docker-compose -f docker compose.yml exec -T app php artisan optimize:clear
docker-compose -f docker compose.yml exec -T app php artisan optimize
