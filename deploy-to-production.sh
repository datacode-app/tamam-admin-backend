#!/bin/bash

# 🚀 Automated TAMAM Production Deployment Script
# Target: prod.tamam.shop (134.209.230.97)
# 
# THIS IS THE MAIN DEPLOYMENT SCRIPT FOR PRODUCTION SERVER
# Usage: ./deploy-to-production.sh
# All production deployments should use this script

set -e  # Exit on any error

# Configuration
SERVER_IP="134.209.230.97"
SERVER_NAME="tamam-production"
SSH_KEY="~/.ssh/tamam_production_key"
REMOTE_PATH="/var/www/tamam"
LOCAL_PATH="."

echo "🚀 Starting TAMAM Admin Panel Deployment to Production Server"
echo "📍 Target: prod.tamam.shop ($SERVER_IP)"
echo "⏰ Started at: $(date)"
echo ""
echo "⚠️  PRODUCTION DEPLOYMENT - PROCEED WITH CAUTION ⚠️"
echo ""

# Step 1: Test SSH Connection
echo "🔐 Step 1: Testing SSH Connection..."
if ssh -i $SSH_KEY -o ConnectTimeout=10 root@$SERVER_IP "echo 'SSH connection successful'" > /dev/null 2>&1; then
    echo "✅ SSH connection verified"
else
    echo "❌ SSH connection failed. Please check SSH key and server status."
    exit 1
fi

# Step 2: Deploy Laravel Core Files
echo ""
echo "📦 Step 2: Deploying Laravel application files..."
rsync -avz --progress \
    --include='app/' --include='app/**' \
    --include='bootstrap/' --include='bootstrap/**' \
    --include='config/' --include='config/**' \
    --include='database/' --include='database/**' \
    --include='public/' --include='public/**' \
    --include='resources/' --include='resources/**' \
    --include='routes/' --include='routes/**' \
    --include='storage/' --include='storage/**' \
    --include='Modules/' --include='Modules/**' \
    --include='vendor/' --include='vendor/**' \
    --include='artisan' \
    --include='composer.json' \
    --include='composer.lock' \
    --exclude='*' \
    -e "ssh -i $SSH_KEY" \
    $LOCAL_PATH/ root@$SERVER_IP:$REMOTE_PATH/

echo "✅ Laravel files deployed successfully"

# Step 3: Deploy Production Environment File
echo ""
echo "🔧 Step 3: Deploying production environment configuration..."
scp -i $SSH_KEY .env.production root@$SERVER_IP:$REMOTE_PATH/.env
echo "✅ Production environment file deployed"

# Step 4: Fix File Permissions
echo ""
echo "🔧 Step 4: Setting proper file permissions..."
ssh -i $SSH_KEY root@$SERVER_IP "
    chown -R www-data:www-data $REMOTE_PATH && 
    chmod -R 755 $REMOTE_PATH && 
    chmod -R 775 $REMOTE_PATH/storage && 
    mkdir -p $REMOTE_PATH/bootstrap/cache && 
    chmod -R 775 $REMOTE_PATH/bootstrap/cache
"
echo "✅ File permissions set correctly"

# Step 5: Update Composer Dependencies
echo ""
echo "📚 Step 5: Installing/Updating Composer dependencies..."
ssh -i $SSH_KEY root@$SERVER_IP "
    cd $REMOTE_PATH && 
    composer install --no-dev --optimize-autoloader --no-interaction
"
echo "✅ Composer dependencies updated"

# Step 6: Clear Laravel Caches
echo ""
echo "🧹 Step 6: Clearing Laravel caches..."
ssh -i $SSH_KEY root@$SERVER_IP "
    cd $REMOTE_PATH && 
    php artisan config:clear &&
    php artisan route:clear &&
    php artisan cache:clear &&
    php artisan view:clear
"
echo "✅ Laravel caches cleared"

# Step 7: Generate Application Key (if needed)
echo ""
echo "🔑 Step 7: Ensuring application key is set..."
ssh -i $SSH_KEY root@$SERVER_IP "
    cd $REMOTE_PATH && 
    if ! grep -q '^APP_KEY=base64:' .env; then 
        php artisan key:generate --force
    fi
"
echo "✅ Application key verified"

# Step 8: Run Database Migrations (Production-safe)
echo ""
echo "🗄️  Step 8: Running database migrations..."
ssh -i $SSH_KEY root@$SERVER_IP "
    cd $REMOTE_PATH && 
    php artisan migrate --force
"
echo "✅ Database migrations completed"

# Step 9: Restart PHP-FPM and Nginx
echo ""
echo "🔄 Step 9: Restarting services..."
ssh -i $SSH_KEY root@$SERVER_IP "
    systemctl restart php8.3-fpm &&
    systemctl restart nginx
"
echo "✅ Services restarted successfully"

# Step 10: Test Production Deployment
echo ""
echo "🧪 Step 10: Testing production deployment..."
echo "Testing admin dashboard route..."
if curl -s -o /dev/null -w "%{http_code}" https://prod.tamam.shop/admin | grep -E "^(200|302)$" > /dev/null; then
    echo "✅ Admin dashboard responding correctly"
else
    echo "⚠️  Admin dashboard test returned unexpected status"
fi

echo "Testing admin login route..."
if curl -s -o /dev/null -w "%{http_code}" https://prod.tamam.shop/login/admin | grep -E "^(200|302)$" > /dev/null; then
    echo "✅ Admin login responding correctly"
else
    echo "⚠️  Admin login test returned unexpected status"
fi

# Final Summary
echo ""
echo "🎉 PRODUCTION DEPLOYMENT COMPLETE!"
echo "📍 Production URL: https://prod.tamam.shop/admin"
echo "📍 Login URL: https://prod.tamam.shop/login/admin"
echo "⏰ Completed at: $(date)"
echo ""
echo "🔍 Next Steps:"
echo "  1. Test admin login functionality thoroughly"
echo "  2. Verify database connectivity and data integrity"
echo "  3. Check all admin routes are working properly"
echo "  4. Monitor server logs for any issues"
echo "  5. Perform smoke tests on critical functionality"
echo ""
echo "🚨 IMPORTANT: Monitor application closely for the first 30 minutes"