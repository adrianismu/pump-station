#!/bin/bash

# Set proper permissions
chmod -R 755 /app
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Wait for database to be ready
echo "Waiting for database connection..."
timeout=30
while ! php artisan migrate:status > /dev/null 2>&1; do
    echo "Database not ready yet, waiting..."
    sleep 2
    timeout=$((timeout - 2))
    if [ $timeout -le 0 ]; then
        echo "Database connection timeout"
        break
    fi
done

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Run database seeders
echo "Running database seeders..."
php artisan db:seed --force

# Clear and cache configurations
echo "Optimizing application..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Start the web server
echo "Starting web server..."
exec php artisan serve --host=0.0.0.0 --port=$PORT 