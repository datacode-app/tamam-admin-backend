#!/bin/bash

# 🔧 Production Environment Creator
# Creates .env file on production server with proper credentials
# This script should be run ON the production server
# Uses Production MySQL Cluster (DigitalOcean) as per branch strategy

echo "🔧 Creating production environment file..."

cat > /var/www/tamam/.env << 'EOF'
APP_NAME=Laravel
APP_ENV=production
APP_KEY=base64:EvCdLnWM9f4hNotbvmwTo1w+PM2693O9gyknfhNozHs=
APP_DEBUG=false
APP_URL=https://prod.tamam.shop

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Production Database Configuration
DB_CONNECTION=mysql
DB_HOST=tamam-production-db-do-user-19403128-0.j.db.ondigitalocean.com
DB_PORT=25060
DB_DATABASE=tamamdb
DB_USERNAME=doadmin
DB_PASSWORD=PRODUCTION_DB_PASSWORD_PLACEHOLDER

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DRIVER=s3
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Production Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@tamam.shop
MAIL_FROM_NAME="${APP_NAME}"

# Production DigitalOcean Spaces Configuration
AWS_ACCESS_KEY_ID=PRODUCTION_SPACES_KEY_PLACEHOLDER
AWS_SECRET_ACCESS_KEY=PRODUCTION_SPACES_SECRET_PLACEHOLDER
AWS_DEFAULT_REGION=fra1
AWS_BUCKET=tamam-prod
AWS_ENDPOINT=https://fra1.digitaloceanspaces.com
AWS_USE_PATH_STYLE_ENDPOINT=false
AWS_URL=https://tamam-prod.fra1.cdn.digitaloceanspaces.com

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
EOF

echo "✅ Production environment template created"
echo "⚠️  IMPORTANT: Replace placeholders with actual production credentials!"
echo "📍 Database: Uses Production MySQL Cluster (tamam-production-db-do-user-19403128-0.j.db.ondigitalocean.com)"
echo "📍 Storage: Uses Production DigitalOcean Spaces (tamam-prod bucket)"