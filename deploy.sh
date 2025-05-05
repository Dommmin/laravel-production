#!/bin/bash

# Exit on error
set -e

echo "🚀 Starting deployment..."

# Pull latest Docker images
echo "📥 Pulling latest Docker images..."
docker compose pull

# Restart containers
echo "🔄 Restarting containers..."
docker compose down
docker compose up -d

# Run Laravel commands
echo "⚡ Running Laravel commands..."
php artisan optimize:clear
php artisan optimize
php artisan storage:link
php artisan migrate --force

# Clean up old releases
echo "🧹 Cleaning up old releases..."
docker system prune -f

echo "✅ Deployment completed successfully!"
