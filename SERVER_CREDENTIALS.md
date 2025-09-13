# Server Access Credentials

## Production Server
- **Domain**: `prod.tamam.shop`
- **IP Address**: `134.209.230.97`
- **SSH Key**: `~/.ssh/tamam_production_key`
- **Username**: `root`
- **Application Path**: `/var/www/tamam`
- **SSH Command**: `ssh -i ~/.ssh/tamam_production_key root@134.209.230.97`

### Production Log Locations
- **Laravel Logs**: `/var/www/tamam/storage/logs/laravel.log`
- **Nginx Error Logs**: `/var/log/nginx/error.log`
- **Nginx Access Logs**: `/var/log/nginx/access.log`
- **PHP-FPM Logs**: `/var/log/php8.3-fpm.log`
- **System Logs**: `/var/log/syslog`

## Staging Server
- **Domain**: `staging.tamam.shop`
- **IP Address**: `46.101.190.171`
- **SSH Key**: `~/.ssh/tamam_staging_key`
- **Username**: `root`
- **Application Path**: `/var/www/tamam`
- **SSH Command**: `ssh -i ~/.ssh/tamam_staging_key root@46.101.190.171`

### Staging Log Locations
- **Laravel Logs**: `/var/www/tamam/storage/logs/laravel.log`
- **Nginx Error Logs**: `/var/log/nginx/error.log`
- **Nginx Access Logs**: `/var/log/nginx/access.log`
- **PHP-FPM Logs**: `/var/log/php8.3-fpm.log`
- **System Logs**: `/var/log/syslog`

## Quick Log Checking Commands

### Using the Log Monitoring Script
```bash
# Check production logs
./check-production-logs.sh laravel 50    # Laravel logs (50 lines)
./check-production-logs.sh nginx 30      # Nginx logs (30 lines)
./check-production-logs.sh all 20        # All logs (20 lines each)

# Monitor logs in real-time
./check-production-logs.sh monitor laravel
./check-production-logs.sh monitor nginx
```

### Direct SSH Commands
```bash
# Production server Laravel logs
ssh -i ~/.ssh/tamam_production_key root@134.209.230.97 "tail -f /var/www/tamam/storage/logs/laravel.log"

# Production server Nginx error logs
ssh -i ~/.ssh/tamam_production_key root@134.209.230.97 "tail -f /var/log/nginx/error.log"

# Staging server Laravel logs
ssh -i ~/.ssh/tamam_staging_key root@46.101.190.171 "tail -f /var/www/tamam/storage/logs/laravel.log"

# Staging server Nginx error logs
ssh -i ~/.ssh/tamam_staging_key root@46.101.190.171 "tail -f /var/log/nginx/error.log"
```

## Database Information

### Production Database
- **Host**: `tamam-production-db-do-user-19403128-0.j.db.ondigitalocean.com`
- **Port**: `25060`
- **Database**: `tamamdb`
- **Username**: `doadmin`
- **Password**: Stored in GitHub secrets as `PRODUCTION_DB_PASSWORD`

### DigitalOcean Spaces (Production)
- **Bucket**: `tamam-prod`
- **Region**: `fra1`
- **Endpoint**: `https://fra1.digitaloceanspaces.com`
- **CDN URL**: `https://tamam-prod.fra1.cdn.digitaloceanspaces.com`
- **Access Key**: Stored in GitHub secrets as `PRODUCTION_SPACES_KEY`
- **Secret Key**: Stored in GitHub secrets as `PRODUCTION_SPACES_SECRET`

## GitHub Secrets Available
- `PRODUCTION_SSH_KEY`
- `PRODUCTION_SERVER_IP`
- `PRODUCTION_DB_PASSWORD`
- `PRODUCTION_SPACES_KEY`
- `PRODUCTION_SPACES_SECRET`
- `STAGING_SSH_KEY`
- `STAGING_SERVER_IP`

## Service Management Commands
```bash
# Restart services on production
ssh -i ~/.ssh/tamam_production_key root@134.209.230.97 "systemctl restart php8.3-fpm && systemctl restart nginx"

# Restart services on staging
ssh -i ~/.ssh/tamam_staging_key root@46.101.190.171 "systemctl restart php8.3-fpm && systemctl restart nginx"

# Check service status
ssh -i ~/.ssh/tamam_production_key root@134.209.230.97 "systemctl status nginx php8.3-fpm"
```

## Laravel Commands on Servers
```bash
# Production Laravel commands
ssh -i ~/.ssh/tamam_production_key root@134.209.230.97 "cd /var/www/tamam && php artisan route:list"
ssh -i ~/.ssh/tamam_production_key root@134.209.230.97 "cd /var/www/tamam && php artisan cache:clear"
ssh -i ~/.ssh/tamam_production_key root@134.209.230.97 "cd /var/www/tamam && php artisan config:clear"

# Staging Laravel commands
ssh -i ~/.ssh/tamam_staging_key root@46.101.190.171 "cd /var/www/tamam && php artisan route:list"
ssh -i ~/.ssh/tamam_staging_key root@46.101.190.171 "cd /var/www/tamam && php artisan cache:clear"
```

---

**Note**: Keep SSH keys secure and never commit them to the repository. All sensitive credentials are stored in GitHub secrets for automated deployments.