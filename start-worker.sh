#!/bin/bash

echo "🔄 Starting Laravel Scheduler Worker for Railway..."

# Set proper environment
export APP_ENV=production
export LOG_CHANNEL=single

# Wait for database to be ready
echo "Waiting for database connection..."
timeout=60
while ! php artisan migrate:status > /dev/null 2>&1; do
    echo "Database not ready yet, waiting..."
    sleep 5
    timeout=$((timeout - 5))
    if [ $timeout -le 0 ]; then
        echo "Database connection timeout, starting anyway..."
        break
    fi
done

# Clear cache
php artisan config:clear
php artisan cache:clear

# Create logs directory if not exists
mkdir -p storage/logs

# Test database connection and notifications
echo "🔍 Testing system before starting scheduler..."
php -r "
try {
    echo 'DB Connection: ' . (DB::connection()->getPdo() ? 'OK' : 'Failed') . PHP_EOL;
    echo 'Notifications table: ' . (Schema::hasTable('notifications') ? 'Exists' : 'Missing') . PHP_EOL;
    echo 'Alerts count: ' . App\Models\Alert::count() . PHP_EOL;
    echo 'Admin users: ' . App\Models\User::role('admin')->count() . PHP_EOL;
    echo 'Queue connection: ' . config('queue.default') . PHP_EOL;
} catch (Exception \$e) {
    echo 'System check failed: ' . \$e->getMessage() . PHP_EOL;
}
"

echo "✅ Starting Laravel scheduler..."
echo "🌦️ Weather alerts will run every 30 minutes"
echo "📧 Notifications will be sent synchronously"

# Log scheduler start
php artisan tinker --execute="Log::info('Scheduler worker started on Railway', ['timestamp' => now(), 'pid' => getmypid()]);"

# Start the scheduler worker with logging
exec php artisan schedule:work --verbose 2>&1 | tee -a storage/logs/scheduler.log 