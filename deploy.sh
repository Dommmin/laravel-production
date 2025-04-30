#!/bin/bash

cd ~/laravel || exit 1

echo "Pulling new Docker images..."
docker compose pull

echo "Restarting containers..."
docker compose up -d

echo "Running database migrations..."
docker compose exec app php artisan migrate --force

echo "Deployment completed."
