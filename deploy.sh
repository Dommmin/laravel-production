#!/bin/bash

cd ~/laravel || exit 1

echo "Setting proper permissions..."
# Ustawienie odpowiednich uprawnień dla katalogów
sudo find public -type d -exec chmod 755 {} \;
sudo find public -type f -exec chmod 644 {} \;
sudo chown -R $USER:$USER public

# Upewnij się, że katalog build istnieje i ma odpowiednie uprawnienia
mkdir -p public/build
sudo chmod 755 public/build

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

# Sprawdź czy pliki build są dostępne
echo "Verifying build files..."
docker compose exec nginx ls -la /var/www/public/build

echo "Deployment completed."
