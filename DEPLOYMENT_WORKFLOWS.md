# GitHub Actions Deployment Workflows

## Overview

This repository contains two comprehensive deployment workflows that align with our branch-specific environment strategy:

- **Staging Deployment**: `.github/workflows/deploy-staging.yml`
- **Production Deployment**: `.github/workflows/deploy-production.yml`

## Workflow Features

### 🚀 Staging Deployment Workflow

**Triggers**: Push to `staging` branch or manual dispatch
**Target**: staging.tamam.shop (Staging Server)
**Database**: Staging MySQL Cluster (shared with main branch)

#### Key Features:
- ✅ Comprehensive rollback point creation
- ✅ Uses staging MySQL cluster as per environment strategy
- ✅ Module activation verification (`modules_statuses.json`)
- ✅ Database migration with verification
- ✅ Multi-endpoint testing with error reporting
- ✅ System health monitoring
- ✅ Detailed error logging and debugging
- ✅ Storage integration with tamam-staging DigitalOcean Space

#### Workflow Steps:
1. **Repository Checkout** - Get latest staging code
2. **SSH Setup** - Configure secure server access
3. **Rollback Preparation** - Create backup point for safety
4. **File Deployment** - Rsync Laravel files to staging server
5. **Environment Configuration** - Deploy staging-specific .env using `create-staging-env.sh`
6. **Permissions Setup** - Set proper file and directory permissions
7. **Dependencies Update** - Install/update Composer packages
8. **Cache Management** - Clear Laravel caches (config, route, cache, view)
9. **Module Verification** - Verify module activation and status
10. **Database Operations** - Run migrations and seeders with verification
11. **App Key Management** - Generate or verify application key
12. **Service Restart** - Restart PHP-FPM and Nginx services
13. **Comprehensive Testing** - Test all critical admin endpoints
14. **Health Monitoring** - Check system resources and logs
15. **Summary Report** - Provide deployment status and next steps

### 🚨 Production Deployment Workflow

**Triggers**: Push to `prod` branch or manual dispatch
**Target**: prod.tamam.shop (Production Server) 
**Database**: Production MySQL Cluster (ISOLATED from staging)

#### Key Features:
- ⚠️ Enhanced production warnings and safety measures
- 🔒 Uses isolated production MySQL cluster
- 🔄 Timestamped rollback points for production safety
- 🧪 Multi-attempt endpoint testing (3 attempts per endpoint)
- 📊 Comprehensive system health reporting
- 🚨 Critical monitoring requirements and success criteria
- ☁️ Production DigitalOcean Spaces integration (tamam-prod)

#### Workflow Steps:
1. **Repository Checkout** - Get latest production code
2. **SSH Setup** - Configure secure production server access  
3. **Production Warnings** - Display critical deployment warnings
4. **Rollback Preparation** - Create timestamped backup for production safety
5. **File Deployment** - Rsync Laravel files to production server
6. **Environment Configuration** - Deploy production-specific .env using `create-production-env.sh`
7. **Permissions Setup** - Set proper production file permissions
8. **Dependencies Update** - Install production-optimized Composer packages
9. **Cache Management** - Clear production Laravel caches
10. **Module Verification** - Verify production module activation
11. **Database Operations** - Execute production migrations with extreme caution
12. **App Key Management** - Verify production application key
13. **Service Restart** - Restart production services
14. **Critical Testing** - Multi-attempt testing of all production endpoints
15. **System Health Check** - Comprehensive production health monitoring
16. **Production Summary** - Critical monitoring requirements and rollback plan

## Required GitHub Secrets

### Staging Environment
- `STAGING_SSH_KEY` - SSH private key for staging server access
- `STAGING_SERVER_IP` - IP address of staging server
- `STAGING_DB_PASSWORD` - Password for staging MySQL cluster
- `STAGING_SPACES_KEY` - DigitalOcean Spaces access key for staging
- `STAGING_SPACES_SECRET` - DigitalOcean Spaces secret key for staging

### Production Environment  
- `PRODUCTION_SSH_KEY` - SSH private key for production server access
- `PRODUCTION_SERVER_IP` - IP address of production server
- `PRODUCTION_DB_PASSWORD` - Password for production MySQL cluster
- `PRODUCTION_SPACES_KEY` - DigitalOcean Spaces access key for production
- `PRODUCTION_SPACES_SECRET` - DigitalOcean Spaces secret key for production

## Environment Strategy Integration

### Database Cluster Usage
- **Staging**: Uses staging MySQL cluster (`tamam-staging-db-do-user-19403128-0.j.db.ondigitalocean.com`)
- **Production**: Uses isolated production MySQL cluster (`tamam-production-db-do-user-19403128-0.j.db.ondigitalocean.com`)

### Storage Configuration
- **Staging**: `tamam-staging` DigitalOcean Space
- **Production**: `tamam-prod` DigitalOcean Space

### Environment Files
- **Staging**: Uses `create-staging-env.sh` with staging cluster configuration
- **Production**: Uses `create-production-env.sh` with production cluster configuration

## Deployment Process

### Staging Deployment
```bash
# Automatic: Push to staging branch
git push origin staging

# Manual: Trigger via GitHub Actions UI
# Go to Actions → Deploy to Staging Server → Run workflow
```

### Production Deployment
```bash
# Automatic: Push to prod branch (USE WITH EXTREME CAUTION)
git push origin prod

# Manual: Trigger via GitHub Actions UI (RECOMMENDED)
# Go to Actions → Deploy to Production Server → Run workflow
```

## Safety Features

### Staging Safety
- Automatic rollback points before deployment
- Comprehensive endpoint testing
- System health monitoring
- Detailed error reporting and logging

### Production Safety
- ⚠️ Enhanced warnings and confirmation requirements
- Timestamped rollback points for version tracking
- Multi-attempt endpoint testing (3 attempts per endpoint)
- Comprehensive system health monitoring
- Critical monitoring requirements documentation
- Rollback plan with database consideration
- 30+ minute monitoring requirement post-deployment

## Error Handling

### Test Failures
Both workflows will **fail and exit** if:
- SSH connection cannot be established
- Critical endpoints return non-200/302 status codes
- Database operations fail
- Module verification fails

### Recovery Procedures
1. **Staging Issues**: 
   - Check staging server logs
   - Use rollback point at `/var/www/tamam_backup`
   - Review environment configuration

2. **Production Issues**:
   - **IMMEDIATE INVESTIGATION REQUIRED**
   - Check production logs via workflow output
   - Consider rollback using timestamped backup
   - Review database changes manually if needed
   - Monitor system resources

## Monitoring Requirements

### Post-Staging Deployment
- ✅ Test admin login functionality  
- ✅ Verify database connectivity
- ✅ Test module functionality
- ✅ Validate file uploads and storage
- ✅ Check all admin routes and permissions

### Post-Production Deployment (CRITICAL)
- 🚨 **Monitor for 30+ minutes continuously**
- 🚨 Test ALL critical business operations
- 🚨 Verify admin login and user management
- 🚨 Test order processing and vendor management  
- 🚨 Validate file uploads and storage operations
- 🚨 Check payment gateway integrations
- 🚨 Monitor server resources (CPU, memory, disk)
- 🚨 Watch Laravel logs for errors/warnings

## Success Criteria

### Staging Success
- All admin routes responding (200/302)
- Database connectivity confirmed
- Module activation working
- File storage operational
- System resources stable

### Production Success
- All admin routes responding correctly
- Database connectivity confirmed  
- File storage operations working
- No critical errors in logs
- System resources stable
- All business operations functional

## Workflow Validation Results

✅ **Staging Workflow**: All required features validated
- SSH Setup: ✅
- Environment Creation: ✅  
- Module Verification: ✅
- Database Migration: ✅
- Endpoint Testing: ✅

✅ **Production Workflow**: All required features validated
- SSH Setup: ✅
- Environment Creation: ✅
- Module Verification: ✅
- Database Migration: ✅
- Production Warnings: ✅
- Rollback Points: ✅

## Emergency Contacts & Procedures

In case of **PRODUCTION ISSUES**:
1. Check GitHub Actions workflow logs immediately
2. SSH into production server for direct diagnosis
3. Consider immediate rollback if critical functionality affected
4. Document any issues for post-incident review

**Production Rollback Command**:
```bash
ssh root@$PRODUCTION_SERVER_IP "cd /var/www && rm -rf tamam && mv tamam_backup_TIMESTAMP tamam && systemctl restart php8.3-fpm nginx"
```

---

**Last Updated**: $(date)  
**Environment Strategy**: Branch-specific database cluster isolation  
**Workflow Version**: v2.0 (Comprehensive deployment with safety features)