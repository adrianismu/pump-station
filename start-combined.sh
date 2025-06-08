#!/bin/bash

# Set proper permissions
chmod -R 755 /app
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Create storage link for public access
echo "Creating storage link..."
php artisan storage:link

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

# Check if FRESH_DB is set to reset database
if [ "$FRESH_DB" = "true" ]; then
    echo "🔄 Fresh database migration requested..."
    php artisan migrate:fresh --force
    echo "🌱 Running fresh seeders..."
    php artisan db:seed --force
else
    # Run database migrations
    echo "Running database migrations..."
    php artisan migrate --force

    # Run database seeders
    echo "Running database seeders..."
    php artisan db:seed --force
fi

# Clear and cache configurations
echo "Optimizing application..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Create logs directory if not exists
mkdir -p storage/logs

# Start Laravel scheduler in background
echo "🌦️ Starting Laravel scheduler for weather alerts..."
php artisan schedule:work &

# Start the web server
echo "🚀 Starting web server..."
exec php artisan serve --host=0.0.0.0 --port=$PORT 