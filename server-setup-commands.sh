#!/bin/bash

# 🔧 Production Server SSH Key Setup Commands
# Run these commands on the production server (134.209.230.97)

echo "🔧 Setting up SSH key for production server..."

# Step 1: Create SSH directory with proper permissions
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Step 2: Add SSH key using file input (safer than echo)
cat > ~/.ssh/authorized_keys << 'EOF'
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQC5QXkOyJKeXrvpUYBRfh+adqFTXGkXNCZHaxuVpF6mQc1cmHX42olkK0nt/e6k1Qek2BKpjOVwRKFKLhu3s8wryoMHBOayc0m/dVUlsqf4QXgzp9j8mSSA/wsK+tIPPNm2tXZ524BW1E0ZUYYb7DG3Grltomlv1YlhP4b6Z6V749ovNWpf3c4Qwoq2xIgHUBajGb89ErUZZ4oIIfSXeefSPaYsG4L6fKm1cO174VBOkn3ZRSbq3Mkx3LVBhgxdaSvYXTX+R/qvhqfVFBKLN14Cxkul1Cu7CghpJJnjmqiljFTYkmk2wcyrwluT5VQqAtMfiVCFH00ACXadOyyxr8wno/oHxcA0aFVtbVNaA99+AJva3C3Gu1SEEHL3LX3parVgFbX4N3yG+zixV55XWgSY6xmd8+n3CBhT0GwdbctUxL082B0kTf6B1vvu5ogNlGlR2yP3+a/twogCdPskUze+Y9VhMQiCQsb19Jbmx2sO5Wdz9O1AMPFicl+eaNuzQUudF5LAMYW5Tl396NIeER9M2CRxz6Sq/TPa0LMCV+ygHqx+MOEM6N1wQiR9+njZg4i0AE52ROYedQrNYSK1qwIm6LmxC7aebjM8neGUa9PzqIh7C+xPwuxO18Eg4FEyUsjLEBM0ouKOgg7H0M79N5203kkhQLu6WD45Ef6hmUdz2w== production-server-tamam
EOF

# Step 3: Set proper permissions
chmod 600 ~/.ssh/authorized_keys

echo "✅ SSH key setup complete!"

# Step 4: Install server components
echo "🚀 Installing server components..."
apt update && apt upgrade -y

apt install -y nginx php8.3-fpm php8.3-mysql php8.3-xml php8.3-curl \
    php8.3-mbstring php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl \
    unzip curl wget software-properties-common certbot python3-certbot-nginx

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Create application directory
mkdir -p /var/www/tamam
chown -R www-data:www-data /var/www/tamam

# Configure Nginx for prod.tamam.shop
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
ln -sf /etc/nginx/sites-available/prod.tamam.shop /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test and restart services
nginx -t
systemctl enable nginx php8.3-fpm
systemctl restart nginx php8.3-fpm

echo "🎉 Production server setup complete!"
echo "✅ SSH key configured"
echo "✅ LAMP stack installed"
echo "✅ Nginx configured for prod.tamam.shop"
echo ""
echo "🔧 Next steps:"
echo "1. Install SSL certificate: certbot --nginx -d prod.tamam.shop"
echo "2. Test deployment from GitHub Actions"