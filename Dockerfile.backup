# Multi-stage build for better optimization
FROM php:8.2-cli as builder

# Install system dependencies for building
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies with verbose output for debugging
RUN composer install --no-dev --optimize-autoloader --no-interaction --verbose

# Copy package.json files
COPY package*.json ./

# Install Node dependencies and build assets
RUN npm ci --only=production && npm run build && npm cache clean --force

# Copy application code
COPY . .

# Production stage
FROM php:8.2-apache as production

# Install runtime dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy built application from builder stage
COPY --from=builder /var/www/html .

# Copy Apache configuration
COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Create startup script for Railway
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Update Apache port configuration\n\
if [ ! -z "$PORT" ]; then\n\
    sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf\n\
    sed -i "s/:80/:$PORT/" /etc/apache2/sites-available/000-default.conf\n\
fi\n\
\n\
# Wait for database to be ready\n\
echo "Waiting for database..."\n\
sleep 10\n\
\n\
# Run Laravel optimizations\n\
php artisan config:cache || echo "Config cache failed"\n\
php artisan route:cache || echo "Route cache failed"\n\
php artisan view:cache || echo "View cache failed"\n\
\n\
# Start Apache\n\
exec apache2-foreground' > /start.sh && chmod +x /start.sh

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:${PORT:-80}/ || exit 1

# Start with custom script
CMD ["/start.sh"] 