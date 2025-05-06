#!/bin/bash

# Exit on error
set -e

# Load environment variables
source .env

# Create a unique deployment ID
DEPLOYMENT_ID=${DEPLOYMENT_ID:-$(date +%s)}
echo "🚀 Starting deployment $DEPLOYMENT_ID"

# Find an available port
find_available_port() {
    local port=8080
    while netstat -tuln | grep -q ":$port "; do
        port=$((port + 1))
    done
    echo $port
}

# Get current port if running
CURRENT_PORT=$(docker compose ps nginx --format json | jq -r '.[0].Ports' | grep -oP '\d+(?=->80/tcp)' || echo "80")
NEW_PORT=$(find_available_port)

echo "📊 Current port: $CURRENT_PORT, New port: $NEW_PORT"

# Pull the latest images
echo "📥 Pulling latest Docker images..."
docker compose pull

# Create new containers with unique names and new port
echo "🏗️ Creating new containers..."
NGINX_PORT=$NEW_PORT docker compose -p laravel_${DEPLOYMENT_ID} up -d --no-deps --scale app=2

# Wait for new containers to be healthy with timeout
echo "⏳ Waiting for new containers to be healthy..."
TIMEOUT=60
ELAPSED=0
while [ $ELAPSED -lt $TIMEOUT ]; do
    if docker compose -p laravel_${DEPLOYMENT_ID} ps | grep -q "healthy"; then
        echo "✅ All containers are healthy!"
        break
    fi
    echo "⏳ Still waiting for containers to be healthy... ($ELAPSED/$TIMEOUT seconds)"
    sleep 5
    ELAPSED=$((ELAPSED + 5))
done

if [ $ELAPSED -ge $TIMEOUT ]; then
    echo "❌ Timeout waiting for containers to be healthy"
    docker compose -p laravel_${DEPLOYMENT_ID} ps
    docker compose -p laravel_${DEPLOYMENT_ID} logs
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
NGINX_PORT=80 docker compose up -d

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
