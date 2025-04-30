#!/bin/bash

cd ~/laravel || exit 1

echo "Setting proper permissions..."
# Ustawienie odpowiednich uprawnień dla katalogów
sudo find public -type d -exec chmod 755 {} \;
sudo find public -type f -exec chmod 644 {} \;
sudo chown -R $USER:$USER public

echo "Pulling new Docker images..."
docker compose pull

echo "Restarting containers..."
docker compose down
docker compose up -d

echo "Running database migrations..."
docker compose exec app php artisan migrate --force

# Napraw uprawnienia po uruchomieniu kontenerów
echo "Fixing permissions in containers..."
docker compose exec nginx chown -R nginx:nginx /var/www/public
docker compose exec app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

echo "Deployment completed."
