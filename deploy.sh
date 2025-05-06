#!/bin/bash

# Exit on error
set -e

# Load environment variables
source .env

# Create a unique deployment ID
DEPLOYMENT_ID=${DEPLOYMENT_ID:-$(date +%s)}
echo "🚀 Starting deployment $DEPLOYMENT_ID"

# Pull the latest images
echo "📥 Pulling latest Docker images..."
docker compose pull

# Create new containers with unique names
echo "🏗️ Creating new containers..."
docker compose -p laravel_${DEPLOYMENT_ID} up -d --no-deps --scale app=2 --no-recreate

# Wait for new containers to be healthy
echo "⏳ Waiting for new containers to be healthy..."
sleep 10

# Check if new containers are running properly
if ! docker compose -p laravel_${DEPLOYMENT_ID} ps | grep -q "Up"; then
    echo "❌ New containers failed to start properly"
    docker compose -p laravel_${DEPLOYMENT_ID} down
    exit 1
fi

# Get the old project name
OLD_PROJECT=$(docker compose ls --format json | jq -r '.[0].name' | grep -v "laravel_${DEPLOYMENT_ID}" || true)

if [ ! -z "$OLD_PROJECT" ]; then
    echo "🔄 Switching traffic to new containers..."
    
    # Stop old containers gracefully
    echo "🛑 Stopping old containers..."
    docker compose -p $OLD_PROJECT down --remove-orphans
fi

# Remove the deployment ID from the project name for the final state
echo "✨ Finalizing deployment..."
docker compose -p laravel_${DEPLOYMENT_ID} down
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
