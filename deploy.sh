#!/bin/bash

# Exit on error
set -e

echo "🚀 Starting deployment..."

# Pull latest Docker images
echo "📥 Pulling latest Docker images..."
docker compose pull

if [ "$(docker ps -q --filter "name=app")" ]; then
echo "🔄 Restarting containers..."
    docker compose down
fi

docker compose up -d

# Run Laravel commands
echo "⚡ Running Laravel commands..."
docker compose exec -T app php artisan optimize:clear
docker compose exec -T app php artisan optimize
docker compose exec -T app php artisan storage:link
docker compose exec -T app php artisan migrate --force

# Clean up old releases
echo "🧹 Cleaning up old releases..."
docker system prune -f

echo "✅ Deployment completed successfully!"
