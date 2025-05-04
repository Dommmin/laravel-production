#!/bin/bash

cd ~/laravel || exit 1

# Tworzenie niezbędnych katalogów jeśli nie istnieją
mkdir -p storage/app/public
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p public/build

# Ustawienie odpowiednich uprawnień
echo "Setting proper permissions..."
# Ustawienie właściciela i uprawnień dla storage i cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Upewnij się, że katalogi framework mają odpowiednie uprawnienia
sudo chmod -R 775 storage/framework
sudo chown -R www-data:www-data storage/framework

# Ustawienie uprawnień dla katalogu public
sudo chown -R www-data:www-data public
sudo chmod -R 755 public
sudo chmod -R 755 public/build

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

# Napraw uprawnienia w kontenerze
echo "Fixing permissions in container..."
docker compose exec app chown -R www-data:www-data /var/www/storage /var/www/public
docker compose exec app chmod -R 775 /var/www/storage
docker compose exec app chmod -R 755 /var/www/public
docker compose exec app chmod -R 755 /var/www/public/build

# Sprawdzenie uprawnień i plików w kontenerze
echo "Verifying permissions and files in containers..."
echo "=== Storage Framework ==="
docker compose exec app ls -la /var/www/storage/framework
echo "=== Public Directory ==="
docker compose exec app ls -la /var/www/public
echo "=== Build Directory ==="
docker compose exec app ls -la /var/www/public/build
echo "=== Build Assets ==="
docker compose exec app ls -la /var/www/public/build/assets

# Sprawdzenie uprawnień w kontenerze nginx
echo "=== Nginx Permissions ==="
docker compose exec nginx ls -la /var/www/public/build/assets

echo "Deployment completed."
