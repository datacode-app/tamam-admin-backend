#!/bin/bash

# 🚀 Production Server Setup Script
# This script configures the production server for TAMAM deployment
# Server: 134.209.230.97 (tamam-production)
# Domain: prod.tamam.shop

echo "🚀 TAMAM Production Server Setup"
echo "================================="
echo "Server: 134.209.230.97 (tamam-production)"
echo "Domain: prod.tamam.shop"
echo ""
echo "⚠️  IMPORTANT: Run this script ON the production server as root"
echo ""

set -e  # Exit on any error

# Update system packages
echo "📦 Step 1: Updating system packages..."
apt update && apt upgrade -y

# Install required packages
echo "📚 Step 2: Installing required packages..."
apt install -y nginx php8.3-fpm php8.3-mysql php8.3-xml php8.3-curl \
    php8.3-mbstring php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl \
    unzip curl wget software-properties-common certbot python3-certbot-nginx

# Install Composer
echo "🎵 Step 3: Installing Composer..."
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

# Create application directory
echo "📁 Step 4: Creating application directories..."
mkdir -p /var/www/tamam
chown -R www-data:www-data /var/www/tamam

# Configure PHP-FPM
echo "⚙️  Step 5: Configuring PHP-FPM..."
systemctl enable php8.3-fpm
systemctl start php8.3-fpm

# Create Nginx virtual host for prod.tamam.shop
echo "🌐 Step 6: Creating Nginx virtual host..."
cat > /etc/nginx/sites-available/prod.tamam.shop << 'EOF'
server {
    listen 80;
    server_name prod.tamam.shop;
    root /var/www/tamam/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Enable the site
echo "✅ Step 7: Enabling Nginx site..."
ln -sf /etc/nginx/sites-available/prod.tamam.shop /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
nginx -t

# Start/restart services
echo "🔄 Step 8: Starting services..."
systemctl enable nginx
systemctl restart nginx
systemctl restart php8.3-fpm

# Configure firewall
echo "🔥 Step 9: Configuring firewall..."
ufw allow 'Nginx Full'
ufw allow ssh

echo ""
echo "✅ Basic server setup complete!"
echo ""
echo "🔍 Next manual steps:"
echo "1. Configure DNS: Point prod.tamam.shop to 134.209.230.97"
echo "2. Install SSL certificate:"
echo "   sudo certbot --nginx -d prod.tamam.shop"
echo "3. Add your SSH public key:"
echo "   mkdir -p ~/.ssh"
echo "   echo 'YOUR_SSH_PUBLIC_KEY' >> ~/.ssh/authorized_keys"
echo "   chmod 700 ~/.ssh && chmod 600 ~/.ssh/authorized_keys"
echo "4. Test deployment with: ./deploy-to-production.sh"
echo ""
echo "🚨 IMPORTANT: Make sure to configure DNS before installing SSL!"