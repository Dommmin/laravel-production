#!/bin/bash

cd ~/laravel || exit 1

# Tworzenie niezbędnych katalogów jeśli nie istnieją
mkdir -p storage/app/public
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Ustawienie odpowiednich uprawnień
echo "Setting proper permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo "Pulling new Docker images..."
docker compose pull

echo "Restarting containers..."
docker compose down
docker compose up -d

echo "Running database migrations..."
docker compose exec app php artisan migrate --force

# Tworzenie linku symbolicznego dla storage
echo "Creating storage link..."
docker compose exec app php artisan storage:link

# Sprawdzenie uprawnień w kontenerze
echo "Verifying permissions in containers..."
docker compose exec app ls -la /var/www/public
docker compose exec app ls -la /var/www/public/build

echo "Deployment completed."
