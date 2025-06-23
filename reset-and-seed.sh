#!/bin/bash

echo "🔄 Resetting and re-seeding database..."

# Drop all tables and re-run migrations
echo "📊 Fresh migrations..."
php artisan migrate:fresh --force

# Run all seeders
echo "🌱 Running all seeders..."
php artisan db:seed --force

echo "✅ Database reset and seeding completed!"
echo ""
echo "🔑 Login credentials:"
echo "Admin: admin@pumpstation.com / admin123"
echo "Petugas: [any petugas email] / petugas123" 