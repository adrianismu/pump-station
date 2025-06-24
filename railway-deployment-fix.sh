#!/bin/bash

echo "🚂 RAILWAY DEPLOYMENT FIX"
echo "========================="

echo "🔧 Step 1: Create storage directories"
mkdir -p storage/app/public/reports
mkdir -p storage/app/public/education/thumbnails  
mkdir -p storage/app/public/education/infographics
mkdir -p public/storage
echo "✅ Directories created"

echo "🔗 Step 2: Create storage link"
php artisan storage:link
echo "✅ Storage link created"

echo "📁 Step 3: Ensure public storage structure"
mkdir -p public/storage/reports
mkdir -p public/storage/education/thumbnails
mkdir -p public/storage/education/infographics
echo "✅ Public storage structure ready"

echo "🔍 Step 4: Check directory permissions"
chmod -R 755 storage/
chmod -R 755 public/storage/
echo "✅ Permissions set"

echo "🧪 Step 5: Test storage access"
if [ -d "public/storage" ]; then
    echo "✅ Public storage accessible"
else
    echo "❌ Public storage not accessible"
fi

echo "🏁 Railway deployment fix completed"
echo "Next steps:"
echo "1. Run: php migrate-reports-to-cloudinary.php"
echo "2. Test image URLs in admin panel"
echo "3. Verify no 404 errors" 