#!/bin/bash
set -e

# Upewnij się, że katalogi storage mają prawidłowe uprawnienia
chmod -R 755 /var/www/storage

# Cache konfiguracji i tras
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Uruchom migracje bazy danych (z opcją --force dla środowiska produkcyjnego)
php artisan migrate --force

# Uruchom PHP-FPM w tle
php-fpm &

# Uruchom Supervisord w pierwszym planie (blokuje proces)
exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf 