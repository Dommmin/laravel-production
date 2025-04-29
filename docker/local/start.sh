#!/bin/bash
set -e

# Ustaw prawidłowe uprawnienia dla katalogów aplikacji
echo "Setting correct permissions..."
sudo chown -R www-data:www-data /var/www
sudo chmod -R 775 /var/www

echo "Starting PHP-FPM..."
php-fpm &

if [ ! -d "node_modules" ]; then
  echo "Installing Node dependencies..."
  npm install
fi

echo "Starting Vite..."
npm run dev &

echo "Starting Supervisor..."
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf

wait -n
