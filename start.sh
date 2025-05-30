#!/bin/bash

# Set default port if not provided
export PORT=${PORT:-8000}

# Clear Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Start the application
php artisan serve --host=0.0.0.0 --port=$PORT 