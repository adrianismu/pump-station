#!/bin/bash

# Set proper permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Wait for database to be ready
echo "Waiting for database connection..."
timeout=60
while ! php artisan migrate:status > /dev/null 2>&1; do
    echo "Database not ready yet, waiting..."
    sleep 3
    timeout=$((timeout - 3))
    if [ $timeout -le 0 ]; then
        echo "Database connection timeout, proceeding anyway..."
        break
    fi
done

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Clear and optimize for production
echo "Optimizing application for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache in foreground
echo "Starting Apache web server..."
exec apache2-foreground 