# 🚨 Production Setup Checklist

## ✅ Completed Items

- ✅ **Production Database**: `tamam-production-db` (created)
- ✅ **Production Droplet**: `tamam-production` (134.209.230.97)
- ✅ **Production Spaces Key**: `tamam-production-spaces-key` (created)
- ✅ **Production Storage Bucket**: `tamam-prod` (exists)
- ✅ **Production Environment**: `.env.production` (configured)
- ✅ **Deployment Scripts**: `deploy-to-production.sh` (created)
- ✅ **GitHub Workflow**: `.github/workflows/deploy-production.yml` (created)
- ✅ **Secrets Setup**: `setup-production-secrets.sh` (created)

## 🔄 Manual Steps Required

### 1. ✅ Production Spaces Bucket - COMPLETED
**Status**: ✅ COMPLETED - Bucket already exists

**Existing Configuration**:
- **Provider**: DigitalOcean Spaces (S3-compatible)
- **Name**: `tamam-prod`
- **URL**: https://tamam-prod.fra1.digitaloceanspaces.com
- **CDN URL**: https://tamam-prod.fra1.cdn.digitaloceanspaces.com
- **Region**: Frankfurt (fra1)
- **Access Key**: `DO00Z4JHC9TMVTGTGVWZ` (already configured)
- **Laravel Integration**: Uses 's3' driver with AWS_* variables

**Verification**:
```bash
# Bucket exists and is accessible
curl -I https://tamam-prod.fra1.digitaloceanspaces.com
# Returns: HTTP/2 403 (expected for private bucket)
```

### 2. Generate & Configure Production SSH Key
**Status**: ⚠️ REQUIRED - SSH access needed

**Steps**:
```bash
# Generate production SSH key
ssh-keygen -t rsa -b 4096 -f ~/.ssh/tamam_production_key

# Add public key to production server
ssh-copy-id -i ~/.ssh/tamam_production_key.pub root@134.209.230.97

# Test SSH connection
ssh -i ~/.ssh/tamam_production_key root@134.209.230.97
```

### 3. Configure Production Server
**Status**: ⚠️ REQUIRED - Server setup needed

**Server Setup Commands** (Run on production server):
```bash
# Update system
apt update && apt upgrade -y

# Install required packages
apt install -y nginx php8.3-fpm php8.3-mysql php8.3-xml php8.3-curl php8.3-mbstring php8.3-zip unzip

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Create application directory
mkdir -p /var/www/tamam
chown -R www-data:www-data /var/www/tamam

# Configure Nginx (create virtual host for prod.tamam.shop)
# Configure PHP-FPM
# Setup SSL certificate for prod.tamam.shop
```

### 4. Create Production Branch & Setup GitHub Secrets
**Status**: ⚠️ REQUIRED - Git workflow setup

**Steps**:
```bash
# Create production branch
git checkout -b prod
git push -u origin prod

# Setup production GitHub secrets
./setup-production-secrets.sh
```

## 🔍 Production Infrastructure Overview

```
Production Environment:
├── Domain: prod.tamam.shop
├── Server: 134.209.230.97 (tamam-production)
├── Database: tamam-production-db (fra1)
├── Storage: tamam-prod (fra1) ✅ EXISTS
├── Branch: prod
└── Workflow: deploy-production.yml
```

## 🚨 Security Considerations

- ✅ Separate SSH keys for staging vs production
- ✅ Environment-specific database credentials
- ✅ Production-specific Spaces access keys
- ✅ Debug mode disabled in production
- ⚠️ SSL certificate needed for prod.tamam.shop
- ⚠️ Firewall configuration for production server
- ⚠️ Regular backup strategy for production database

## ⚡ Quick Start After Manual Setup

Once manual steps are complete:

```bash
# 1. Switch to production branch
git checkout prod

# 2. Deploy to production
git add .
git commit -m "production release: initial setup"
git push origin prod  # This triggers production deployment

# 3. Monitor deployment
# Go to GitHub Actions tab and watch the deployment
```

---
**🚨 CRITICAL**: Complete all manual steps before attempting production deployment!