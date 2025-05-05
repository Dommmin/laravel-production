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
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache
docker compose exec -T app php artisan event:cache
docker compose exec -T app php artisan storage:link

echo "✅ Deployment completed successfully!"
