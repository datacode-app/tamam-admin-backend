#!/bin/bash

# 🚀 Automated TAMAM Staging Deployment Script
# Based on successful deployment documentation  
# Target: staging.tamam.shop (46.101.190.171)
# 
# THIS IS THE MAIN DEPLOYMENT SCRIPT FOR STAGING SERVER
# Usage: ./deploy-to-staging.sh
# All routing fixes and Laravel app changes deployed via this script

set -e  # Exit on any error

# Configuration
SERVER_IP="46.101.190.171"
SERVER_NAME="tamam-staging-new"
SSH_KEY="~/.ssh/tamam_staging_key"
REMOTE_PATH="/var/www/tamam"
LOCAL_PATH="."

echo "🚀 Starting TAMAM Admin Panel Deployment to Staging Server"
echo "📍 Target: staging.tamam.shop ($SERVER_IP)"
echo "⏰ Started at: $(date)"
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

# Step 3: Fix File Permissions
echo ""
echo "🔧 Step 3: Setting proper file permissions..."
ssh -i $SSH_KEY root@$SERVER_IP "
    chown -R www-data:www-data $REMOTE_PATH && 
    chmod -R 755 $REMOTE_PATH && 
    chmod -R 775 $REMOTE_PATH/storage && 
    mkdir -p $REMOTE_PATH/bootstrap/cache && 
    chmod -R 775 $REMOTE_PATH/bootstrap/cache
"
echo "✅ File permissions set correctly"

# Step 4: Update Composer Dependencies
echo ""
echo "📚 Step 4: Installing/Updating Composer dependencies..."
ssh -i $SSH_KEY root@$SERVER_IP "
    cd $REMOTE_PATH && 
    composer install --no-dev --optimize-autoloader --no-interaction
"
echo "✅ Composer dependencies updated"

# Step 5: Clear Laravel Caches
echo ""
echo "🧹 Step 5: Clearing Laravel caches..."
ssh -i $SSH_KEY root@$SERVER_IP "
    cd $REMOTE_PATH && 
    php artisan config:clear &&
    php artisan route:clear &&
    php artisan cache:clear &&
    php artisan view:clear
"
echo "✅ Laravel caches cleared"

# Step 6: Generate Application Key (if needed)
echo ""
echo "🔑 Step 6: Ensuring application key is set..."
ssh -i $SSH_KEY root@$SERVER_IP "
    cd $REMOTE_PATH && 
    if ! grep -q '^APP_KEY=base64:' .env; then 
        php artisan key:generate --force
    fi
"
echo "✅ Application key verified"

# Step 7: Restart PHP-FPM and Nginx
echo ""
echo "🔄 Step 7: Restarting services..."
ssh -i $SSH_KEY root@$SERVER_IP "
    systemctl restart php8.3-fpm &&
    systemctl restart nginx
"
echo "✅ Services restarted successfully"

# Step 8: Test Deployment
echo ""
echo "🧪 Step 8: Testing deployment..."
echo "Testing admin dashboard route..."
if curl -s -o /dev/null -w "%{http_code}" https://staging.tamam.shop/admin | grep -E "^(200|302)$" > /dev/null; then
    echo "✅ Admin dashboard responding correctly"
else
    echo "⚠️  Admin dashboard test returned unexpected status"
fi

echo "Testing admin login route..."
if curl -s -o /dev/null -w "%{http_code}" https://staging.tamam.shop/login/admin | grep -E "^(200|302)$" > /dev/null; then
    echo "✅ Admin login responding correctly"
else
    echo "⚠️  Admin login test returned unexpected status"
fi

# Final Summary
echo ""
echo "🎉 DEPLOYMENT COMPLETE!"
echo "📍 Staging URL: https://staging.tamam.shop/admin"
echo "📍 Login URL: https://staging.tamam.shop/login/admin"
echo "⏰ Completed at: $(date)"
echo ""
echo "🔍 Next Steps:"
echo "  1. Test admin login functionality"
echo "  2. Verify database connectivity"
echo "  3. Check all admin routes are working"
echo "  4. Monitor server logs if any issues occur"
echo ""