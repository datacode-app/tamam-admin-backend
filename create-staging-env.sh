#!/bin/bash

# 🔧 Staging Environment Creator
# Creates .env file on staging server with proper credentials
# This script should be run ON the staging server
# Uses Staging MySQL Cluster (DigitalOcean) as per branch strategy

echo "🔧 Creating staging environment file..."

cat > /var/www/tamam/.env << 'EOF'
APP_NAME=Laravel
APP_ENV=staging
APP_KEY=base64:PMl05FDKUiHKrMNi7Z91lPgp8Zbv5z6OnPA42RuYdGw=
APP_DEBUG=false
APP_URL=https://staging.tamam.shop

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=info

# Staging Branch - Uses Staging MySQL Cluster (DigitalOcean)
DB_CONNECTION=mysql
DB_HOST=tamam-staging-db-do-user-19403128-0.j.db.ondigitalocean.com
DB_PORT=25060
DB_DATABASE=tamamdb_staging
DB_USERNAME=doadmin
DB_PASSWORD=STAGING_DB_PASSWORD_PLACEHOLDER

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

# Staging Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@staging.tamam.shop
MAIL_FROM_NAME="${APP_NAME}"

# Staging DigitalOcean Spaces Configuration
AWS_ACCESS_KEY_ID=STAGING_SPACES_KEY_PLACEHOLDER
AWS_SECRET_ACCESS_KEY=STAGING_SPACES_SECRET_PLACEHOLDER
AWS_DEFAULT_REGION=fra1
AWS_BUCKET=tamam-staging
AWS_ENDPOINT=https://fra1.digitaloceanspaces.com
AWS_USE_PATH_STYLE_ENDPOINT=false
AWS_URL=https://tamam-staging.fra1.cdn.digitaloceanspaces.com

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

echo "✅ Staging environment template created"
echo "⚠️  IMPORTANT: Replace placeholders with actual staging credentials!"
echo "📍 Database: Uses Staging MySQL Cluster (tamam-staging-db-do-user-19403128-0.j.db.ondigitalocean.com)"
echo "📍 Storage: Uses Staging DigitalOcean Spaces (tamam-staging bucket)"