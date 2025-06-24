#!/bin/bash

# Railway Storage Fix Script
echo "Railway Deployment with Storage Fix"
echo "==================================="

# Set proper permissions
chmod -R 755 /app
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Create storage link
echo "Creating storage link..."
php artisan storage:link

# Railway storage fix for 404 images
echo "Setting up Railway storage structure..."
mkdir -p storage/app/public/reports
mkdir -p storage/app/public/education/thumbnails  
mkdir -p storage/app/public/education/infographics
mkdir -p public/storage/reports
mkdir -p public/storage/education/thumbnails
mkdir -p public/storage/education/infographics

# Set permissions
chmod -R 755 public/storage/

# Copy files (Railway symlink workaround)
if [ -d "storage/app/public/education" ]; then
    cp -r storage/app/public/education/* public/storage/education/ 2>/dev/null || true
fi

if [ -d "storage/app/public/reports" ]; then
    cp -r storage/app/public/reports/* public/storage/reports/ 2>/dev/null || true
fi

echo "Storage structure ready"

# Wait for database
echo "Waiting for database..."
timeout=30
while ! php artisan migrate:status > /dev/null 2>&1; do
    echo "Database not ready, waiting..."
    sleep 2
    timeout=$((timeout - 2))
    if [ $timeout -le 0 ]; then
        echo "Database timeout"
        break
    fi
done

# Run migrations
if [ "$FRESH_DB" = "true" ]; then
    echo "Running fresh migration..."
    php artisan migrate:fresh --force
    php artisan db:seed --force
else
    echo "Running migrations..."
    php artisan migrate --force
fi

# Optimize
echo "Optimizing application..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Start server
echo "Starting server on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port=$PORT 