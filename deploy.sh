#!/bin/bash

# Exit on error
set -e

echo "🚀 Starting deployment..."

# Pull latest Docker images
echo "📥 Pulling latest Docker images..."
docker compose pull

# Set proper permissions
echo "🔒 Setting permissions..."
mkdir -p storage/app/public
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p public/build

chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Restart containers
echo "🔄 Restarting containers..."
docker compose down
docker compose up -d

# Run Laravel commands
echo "⚡ Running Laravel commands..."
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache
docker compose exec -T app php artisan event:cache
docker compose exec -T app php artisan storage:link

# Verify permissions in containers
echo "🔍 Verifying permissions..."
docker compose exec -T app chown -R www-data:www-data /var/www/storage /var/www/public
docker compose exec -T app chmod -R 755 /var/www/storage
docker compose exec -T app chmod -R 755 /var/www/public
docker compose exec -T app chmod -R 755 /var/www/public/build

echo "✅ Deployment completed successfully!"
