#!/bin/bash
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
until php -r "try {new PDO('mysql:host=mysql;dbname=laravel', 'user', 'password');} catch(PDOException \$e) {echo \$e->getMessage().PHP_EOL; exit(1);}" > /dev/null 2>&1
do
  echo "MySQL is unavailable - sleeping"
  sleep 1
done
echo "MySQL is up - executing commands"

cd /var/www

# Install dependencies
if [ ! -d "/var/www/vendor" ]; then
  echo "Installing dependencies..."
  composer install --no-interaction --no-plugins --no-scripts
fi

# Generate app key if not exists
if [ ! -n "$(grep 'APP_KEY' .env | grep -v '#' | cut -d '=' -f 2)" ]; then
  echo "Generating app key..."
  php artisan key:generate
fi

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Set proper permissions
echo "Setting permissions..."
chown -R www-data:www-data /var/www/storage
chown -R www-data:www-data /var/www/bootstrap/cache

# Clear cache
echo "Clearing cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Bootstrap completed."

# Run the CMD from the Dockerfile
exec "$@" 