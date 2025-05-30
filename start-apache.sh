#!/bin/bash

echo "Starting with Apache..."

# Set default port
export PORT=${PORT:-8000}
echo "Using PORT: $PORT"

# Clear caches
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Set permissions
chmod -R 755 storage bootstrap/cache || true

# Generate key if needed
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force || true
fi

# Create Apache config
cat > /tmp/apache.conf << EOF
Listen $PORT
<VirtualHost *:$PORT>
    DocumentRoot /var/www/html/public
    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
    </Directory>
    ErrorLog /dev/stderr
    CustomLog /dev/stdout combined
</VirtualHost>
EOF

# Start Apache
exec apache2 -D FOREGROUND -f /tmp/apache.conf 