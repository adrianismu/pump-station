#!/bin/bash

echo "🔄 Starting Laravel Scheduler Worker..."

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

# Clear cache
php artisan config:clear
php artisan cache:clear

# Create logs directory if not exists
mkdir -p storage/logs

echo "✅ Starting Laravel scheduler..."
echo "🌦️ Weather alerts will run every hour"

# Start the scheduler worker
exec php artisan schedule:work 