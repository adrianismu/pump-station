#!/bin/bash

# Debug environment
echo "Starting application..."
echo "PORT: $PORT"
echo "APP_ENV: $APP_ENV"

# Set default port if not provided
if [ -z "$PORT" ]; then
    export PORT=8000
    echo "PORT not set, using default: $PORT"
fi

# Ensure PORT is numeric
if ! [[ "$PORT" =~ ^[0-9]+$ ]]; then
    echo "PORT is not numeric: $PORT, using default 8000"
    export PORT=8000
fi

echo "Using PORT: $PORT"

# Clear Laravel caches
echo "Clearing caches..."
php artisan config:clear || echo "Config clear failed"
php artisan cache:clear || echo "Cache clear failed"
php artisan route:clear || echo "Route clear failed"
php artisan view:clear || echo "View clear failed"

# Set proper permissions
echo "Setting permissions..."
chmod -R 755 storage || echo "Storage permissions failed"
chmod -R 755 bootstrap/cache || echo "Bootstrap cache permissions failed"

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating app key..."
    php artisan key:generate --force || echo "Key generation failed"
fi

# Optimize for production
echo "Optimizing for production..."
php artisan config:cache || echo "Config cache failed"
php artisan route:cache || echo "Route cache failed"
php artisan view:cache || echo "View cache failed"

# Start the application
echo "Starting Laravel server on 0.0.0.0:$PORT"
exec php artisan serve --host=0.0.0.0 --port=$PORT 