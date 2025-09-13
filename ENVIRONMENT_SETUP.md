# Environment Configuration Guide

This document explains how environment files are configured across different branches and deployments.

## Branch Environment Strategy

### 🔧 **Main Branch** - Local Development
- **Purpose**: Local development and testing
- **Environment Files**: 
  - `.env.example` - Template for local development
  - `.env.production` - Contains actual production credentials (for reference)
  - `.env.production.example` - Production template with placeholders
- **Local Setup**: Copy `.env.example` to `.env` and configure with local credentials

### 🚀 **Staging Branch** - Staging Environment  
- **Purpose**: Staging server deployments (staging.tamam.shop)
- **Environment Files**:
  - `.env.staging.example` - Staging template with placeholders
  - `create-staging-env.sh` - Staging deployment script with actual credentials
- **Deployment**: `./deploy-to-staging.sh` automatically creates staging `.env` on server

### 🌐 **Prod Branch** - Production Environment
- **Purpose**: Production server deployments (prod.tamam.shop)  
- **Environment Files**:
  - `.env.production.example` - Production template with placeholders
  - `create-production-env.sh` - Production deployment script with actual credentials
- **Deployment**: `./deploy-to-production.sh` automatically creates production `.env` on server

## Environment Differences

| Setting | Local | Staging | Production |
|---------|-------|---------|------------|
| **APP_ENV** | `local` | `staging` | `production` |
| **APP_DEBUG** | `true` | `true` | `false` |
| **APP_URL** | `localhost:8000` | `staging.tamam.shop` | `prod.tamam.shop` |
| **LOG_LEVEL** | `debug` | `debug` | `error` |
| **Database** | Local MySQL | Production DB (shared) | Production DB |
| **Storage Bucket** | Local/S3 | `tamam-staging` | `tamam-prod` |

## Database Configuration

### Production Database (Shared)
- **Host**: `tamam-production-db-do-user-19403128-0.j.db.ondigitalocean.com`
- **Port**: `25060`
- **Database**: `tamamdb`
- **Username**: `doadmin` 
- **Password**: `AVNS_biT5d15EkImWV1cfqYO`

**Note**: Staging and production share the same database cluster for consistency.

## DigitalOcean Spaces Configuration

### Shared Credentials
- **Access Key**: `DO00Z4JHC9TMVTGTGVWZ`
- **Secret Key**: `5yVY4d8XOppSQfH7YRINRpY9jZ0UU/dw3+3GhdiYCro`
- **Region**: `fra1`
- **Endpoint**: `https://fra1.digitaloceanspaces.com`

### Environment-Specific Buckets
- **Staging**: `tamam-staging` → `https://tamam-staging.fra1.cdn.digitaloceanspaces.com`
- **Production**: `tamam-prod` → `https://tamam-prod.fra1.cdn.digitaloceanspaces.com`

## Deployment Environment Creation

### Staging Deployment
```bash
# Deploys to staging.tamam.shop (46.101.190.171)
./deploy-to-staging.sh

# What it does:
# 1. Syncs Laravel files
# 2. Runs create-staging-env.sh on server
# 3. Creates .env with staging-specific settings
# 4. Configures tamam-staging bucket
# 5. Enables debug mode
```

### Production Deployment  
```bash
# Deploys to prod.tamam.shop (134.209.230.97)
./deploy-to-production.sh

# What it does:
# 1. Syncs Laravel files  
# 2. Runs create-production-env.sh on server
# 3. Creates .env with production-specific settings
# 4. Configures tamam-prod bucket
# 5. Disables debug mode
```

## Security Notes

### ✅ Safe Practices
- No actual credentials committed to git (only in deployment scripts)
- Template files use placeholders for sensitive values
- Each environment has separate storage buckets
- Production has debug mode disabled

### 🔒 Credential Storage
- **Local Development**: Use `.env` file (gitignored)
- **Staging/Production**: Created by deployment scripts on servers
- **GitHub Actions**: Uses encrypted secrets for automation

## Local Development Setup

1. **Clone Repository**:
   ```bash
   git clone <repository-url>
   cd tamam-admin-backend
   ```

2. **Setup Local Environment**:
   ```bash
   cp .env.example .env
   # Edit .env with your local database credentials
   ```

3. **Install Dependencies**:
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate
   ```

## Troubleshooting

### Environment Not Loading
- Check if `.env` file exists in server `/var/www/tamam/`
- Verify file permissions: `chmod 644 .env`
- Restart PHP-FPM: `systemctl restart php8.3-fpm`

### Database Connection Errors
- Verify database credentials in environment file
- Check DigitalOcean database cluster status
- Ensure firewall allows connection from server IP

### Storage Issues
- Verify DigitalOcean Spaces credentials
- Check bucket permissions and CORS settings
- Ensure bucket names match environment configuration

---

**Last Updated**: September 13, 2025  
**Contributors**: Claude AI Assistant, Hooshyar