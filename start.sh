#!/bin/bash

# Set proper permissions
chmod -R 755 /app
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Create storage link for public access
echo "Creating storage link..."
php artisan storage:link

# Railway-specific storage fix for image 404 issue
echo "🚂 Setting up Railway storage structure..."
mkdir -p storage/app/public/reports
mkdir -p storage/app/public/education/thumbnails  
mkdir -p storage/app/public/education/infographics
mkdir -p public/storage/reports
mkdir -p public/storage/education/thumbnails
mkdir -p public/storage/education/infographics

# Set storage permissions
chmod -R 755 public/storage/

# Copy existing files to public storage (Railway symlink workaround)
if [ -d "storage/app/public/education" ] && [ "$(ls -A storage/app/public/education 2>/dev/null)" ]; then
    echo "📁 Copying education files to public storage..."
    cp -r storage/app/public/education/* public/storage/education/ 2>/dev/null || true
fi

if [ -d "storage/app/public/reports" ] && [ "$(ls -A storage/app/public/reports 2>/dev/null)" ]; then
    echo "📁 Copying report files to public storage..."
    cp -r storage/app/public/reports/* public/storage/reports/ 2>/dev/null || true
fi

echo "✅ Railway storage structure ready"

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
fi

# Clear and cache configurations
echo "Optimizing application..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Start the web server
echo "Starting web server..."
exec php artisan serve --host=0.0.0.0 --port=$PORT 