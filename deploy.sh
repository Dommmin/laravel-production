#!/bin/bash

# Fail immediately if any command fails
set -eo pipefail

# Deployment header
echo "🚀 Starting production deployment..."
echo "🕒 $(date)"

# Pull latest images
echo "📥 Pulling updated Docker images..."
docker compose pull

# Stop existing containers if running
echo "🛑 Stopping existing containers..."
docker compose down --remove-orphans

# Start fresh containers
echo "🔄 Starting new containers..."
docker compose up -d --force-recreate

# Run application maintenance
echo "🔧 Running application maintenance tasks..."
docker compose exec -T app php artisan optimize:clear
docker compose exec -T app php artisan optimize
docker compose exec -T app php artisan storage:link
docker compose exec -T app php artisan migrate --force

# Cleanup old Docker objects
echo "🧹 Cleaning up unused Docker resources..."
docker system prune --volumes -f

# Success message
echo "✅ Deployment completed successfully!"
echo "🕒 $(date)"
