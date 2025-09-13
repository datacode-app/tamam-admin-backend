# Automated Deployment Setup Guide

## Overview

This guide explains how to set up automated deployment for the TAMAM Admin Backend with automatic module seeding. The system now includes both shell script deployment and GitHub Actions CI/CD.

## 🚀 Deployment Options

### Option 1: GitHub Actions (Recommended)

Automated deployment triggered by git pushes to specific branches.

#### Setup Steps

1. **Add SSH Keys to GitHub Secrets**
   ```bash
   # Go to your GitHub repository
   # Settings → Secrets and variables → Actions
   # Add the following secrets:
   ```

   - `STAGING_SSH_KEY`: Private SSH key for staging server access
   - `PRODUCTION_SSH_KEY`: Private SSH key for production server access

2. **Branch Configuration**
   - `develop` branch → Deploys to staging server
   - `main` branch → Deploys to production server

3. **Automatic Triggers**
   - Push to `develop` → Staging deployment
   - Push to `main` → Production deployment
   - Pull requests → Run tests only

#### What Happens Automatically

1. **Tests Run First**
   - PHP unit tests
   - Database connectivity tests
   - Route validation

2. **Deployment Process**
   - Files are synced to server
   - Composer dependencies updated
   - Database migrations run
   - **ModuleSeeder runs automatically** ✅
   - Caches cleared
   - Services restarted
   - Health checks performed

### Option 2: Manual Shell Scripts

For manual deployments or when GitHub Actions is not available.

#### Usage

```bash
# Deploy to staging
./deploy-to-staging.sh

# Deploy to production
./deploy-to-production.sh
```

#### What's Included

Both scripts now include:
- File synchronization
- Composer dependency updates
- **Automatic database migrations**
- **Automatic module seeding** ✅
- Cache clearing
- Service restarts
- Health checks

## 🔧 Module Seeding Integration

### Automatic Module Creation

The deployment process now automatically creates these modules:

1. **Admin Module** (admin)
2. **Rental Module** (rental)
3. **Food Delivery** (food)
4. **Grocery** (grocery)
5. **Pharmacy** (pharmacy)
6. **E-commerce** (ecommerce)
7. **Parcel Delivery** (parcel)

### Database Operations During Deployment

```bash
# These commands run automatically:
php artisan migrate --force
php artisan db:seed --class=ModuleSeeder --force
```

### Verification

After deployment, verify modules are created:

1. **Check Admin Panel**
   - Go to `/admin/business-settings/module`
   - All modules should be visible and active

2. **Check Database**
   ```sql
   SELECT module_name, module_type, status FROM modules;
   ```

## 📋 Deployment Checklist

### Before Deployment

- [ ] Tests pass locally
- [ ] Database migrations are ready
- [ ] ModuleSeeder is included
- [ ] SSH keys are configured
- [ ] Environment files are updated

### During Deployment

- [ ] Files sync successfully
- [ ] Composer installs without errors
- [ ] Database migrations run
- [ ] ModuleSeeder executes
- [ ] Services restart
- [ ] Health checks pass

### After Deployment

- [ ] Admin panel loads
- [ ] All modules are visible
- [ ] Rental module works
- [ ] Admin module works
- [ ] No errors in logs

## 🔍 Troubleshooting

### Common Issues

1. **Modules Not Created**
   ```bash
   # Manual fix
   php artisan db:seed --class=ModuleSeeder --force
   ```

2. **Database Connection Issues**
   ```bash
   # Check database configuration
   php artisan config:show database
   ```

3. **Permission Issues**
   ```bash
   # Fix permissions
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data .
   ```

### Logs

Check deployment logs:
```bash
# Application logs
tail -f storage/logs/laravel.log

# Server logs
tail -f /var/log/nginx/error.log
```

## 🛡️ Security Considerations

### SSH Key Management

1. **Use dedicated deployment keys**
2. **Rotate keys regularly**
3. **Limit key permissions**
4. **Monitor key usage**

### Environment Security

1. **Never commit .env files**
2. **Use environment-specific configurations**
3. **Secure database credentials**
4. **Enable SSL/TLS**

## 📊 Monitoring

### Health Checks

The deployment scripts include automatic health checks:

- Admin dashboard accessibility
- Login page functionality
- Database connectivity
- Service status

### Monitoring Tools

Consider setting up:
- Application performance monitoring
- Error tracking
- Uptime monitoring
- Database monitoring

## 🔄 Rollback Procedures

### Quick Rollback

```bash
# Revert to previous deployment
git checkout HEAD~1
./deploy-to-production.sh
```

### Database Rollback

```bash
# Rollback migrations
php artisan migrate:rollback --step=1
```

## 📚 Additional Resources

- [Laravel Deployment Guide](https://laravel.com/docs/deployment)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [SSH Key Management](https://docs.github.com/en/authentication/connecting-to-github-with-ssh)

## 🆘 Support

If you encounter issues:

1. Check the deployment logs
2. Verify SSH key permissions
3. Test database connectivity
4. Review environment configuration
5. Check server resources

---

**Note**: This automated deployment system ensures that modules are always created during deployment, eliminating the need for manual intervention.
