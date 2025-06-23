#!/bin/bash

echo "🔄 Resetting and re-seeding Railway database..."

# Set FRESH_DB variable
echo "📝 Setting FRESH_DB variable..."
railway variables --set "FRESH_DB=true"

echo "🚀 Triggering deployment..."
# Force a new deployment by pushing a dummy change
echo "# Fresh DB deployment $(date)" >> .railway-deploy-trigger
git add .railway-deploy-trigger
git commit -m "Trigger fresh DB deployment"
git push origin master

echo "⏳ Waiting for deployment..."
echo "Monitor deployment with: railway logs --follow"
echo ""
echo "⚠️  After deployment completes, run:"
echo "railway variables --set \"FRESH_DB=false\""
echo ""
echo "🔑 Login credentials will be:"
echo "Admin: admin@pumpstation.com / admin123"
echo "Petugas: [any petugas email] / petugas123" 