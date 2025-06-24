#!/bin/bash

echo "🚀 Starting Railway Worker Process..."
echo "Environment: $RAILWAY_ENVIRONMENT_NAME"
echo "Current time: $(date)"

# Function to check database connectivity
check_database() {
    echo "🔍 Checking database connection..."
    
    # Try to connect to database with timeout
    if timeout 30 php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'DB OK'; } catch(Exception \$e) { echo 'DB FAIL: ' . \$e->getMessage(); exit(1); }" 2>/dev/null | grep -q "DB OK"; then
        echo "✅ Database connection successful"
        return 0
    else
        echo "❌ Database connection failed"
        return 1
    fi
}

# Function to wait for database
wait_for_database() {
    local max_attempts=10
    local attempt=1
    
    echo "⏳ Waiting for database to be ready..."
    
    while [ $attempt -le $max_attempts ]; do
        echo "Attempt $attempt/$max_attempts..."
        
        if check_database; then
            echo "✅ Database is ready!"
            return 0
        fi
        
        echo "Database not ready yet, waiting 5 seconds..."
        sleep 5
        attempt=$((attempt + 1))
    done
    
    echo "❌ Database failed to become ready after $max_attempts attempts"
    return 1
}

# Wait for database to be ready
if ! wait_for_database; then
    echo "❌ Cannot start worker: Database is not accessible"
    echo "This is likely a temporary Railway deployment issue"
    echo "Worker will exit gracefully and Railway will restart it"
    exit 0  # Exit with success to prevent restart loop
fi

# Run migrations if needed (with timeout)
echo "🔄 Running database migrations..."
if timeout 60 php artisan migrate --force 2>/dev/null; then
    echo "✅ Migrations completed successfully"
else
    echo "⚠️ Migrations failed or timed out, but continuing..."
fi

# Clear and cache config
echo "🧹 Clearing and caching configuration..."
php artisan config:clear 2>/dev/null || true
php artisan config:cache 2>/dev/null || true

# Test the weather command once
echo "🧪 Testing weather alert command..."
if timeout 60 php artisan app:check-weather-for-alerts 2>/dev/null; then
    echo "✅ Weather command test successful"
else
    echo "⚠️ Weather command test failed, but continuing..."
fi

# Start the main worker loop
echo "🔄 Starting weather monitoring loop..."
echo "Checking weather every 3600 seconds (1 hour)"

while true; do
    current_time=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$current_time] Running weather check..."
    
    # Run weather check with timeout and error handling
    if timeout 300 php artisan app:check-weather-for-alerts; then
        echo "[$current_time] ✅ Weather check completed successfully"
    else
        exit_code=$?
        echo "[$current_time] ⚠️ Weather check failed with exit code: $exit_code"
        
        # If database connection failed, try to reconnect
        if ! check_database; then
            echo "[$current_time] 🔄 Database connection lost, waiting for reconnection..."
            if wait_for_database; then
                echo "[$current_time] ✅ Database reconnected"
            else
                echo "[$current_time] ❌ Database reconnection failed, exiting worker"
                exit 0  # Exit gracefully for Railway to restart
            fi
        fi
    fi
    
    echo "[$current_time] 😴 Sleeping for 1 hour..."
    sleep 3600
done 