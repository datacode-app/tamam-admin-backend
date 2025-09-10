# 🚀 Deployment Quick Reference

## 🏗️ **Staging Environment**

### ⚡ Auto-Deployment (Recommended)
```bash
git add .
git commit -m "your changes"
git push origin main  # 🎯 Triggers automatic staging deployment
```
**Result**: GitHub Actions automatically deploys to staging.tamam.shop

### 🔧 Manual Staging Deployment
```bash
./deploy-to-staging.sh  # Direct staging deployment script
```

### 🔑 Setup Staging GitHub Secrets
```bash
./setup-github-secrets.sh  # Automated staging setup
```

## 🚨 **Production Environment**

### ⚡ Production Auto-Deployment 
```bash
git checkout prod  # Switch to production branch
git add .
git commit -m "production release: your changes"
git push origin prod  # 🎯 Triggers automatic PRODUCTION deployment
```
**Result**: GitHub Actions automatically deploys to prod.tamam.shop

### 🔧 Manual Production Deployment
```bash
./deploy-to-production.sh  # Direct PRODUCTION deployment script
```

### 🔑 Setup Production GitHub Secrets
```bash
./setup-production-secrets.sh  # Automated production setup
```

## 📊 Environment Overview

| Environment | Branch | URL | Server IP | Database |
|-------------|--------|-----|-----------|----------|
| **Staging** | `main` | https://staging.tamam.shop/admin | 46.101.190.171 | tamam-staging-db |
| **Production** | `prod` | https://prod.tamam.shop/admin | 134.209.230.97 | tamam-production-db |

## 🔑 Required GitHub Secrets

### Staging Secrets
| Secret Name | Value | Description |
|-------------|-------|-------------|
| `STAGING_SSH_KEY` | Content of `~/.ssh/tamam_staging_key` | SSH private key for staging server |
| `STAGING_SERVER_IP` | `46.101.190.171` | Staging server IP address |

### Production Secrets
| Secret Name | Value | Description |
|-------------|-------|-------------|
| `PRODUCTION_SSH_KEY` | Content of `~/.ssh/tamam_production_key` | SSH private key for production server |
| `PRODUCTION_SERVER_IP` | `134.209.230.97` | Production server IP address |

## 🧪 Testing After Deployment

### Staging Tests
```bash
curl -I https://staging.tamam.shop/admin        # Should return 302
curl -I https://staging.tamam.shop/login/admin  # Should return 200
curl -I https://staging.tamam.shop/admin/addon  # Should return 302
```

### Production Tests
```bash
curl -I https://prod.tamam.shop/admin        # Should return 302
curl -I https://prod.tamam.shop/login/admin  # Should return 200
curl -I https://prod.tamam.shop/admin/addon  # Should return 302
```

## 📊 What Gets Deployed
- ✅ Laravel application files (app/, routes/, config/, etc.)
- ✅ Environment-specific configuration (.env)
- ✅ Composer dependencies updated
- ✅ File permissions fixed
- ✅ Laravel caches cleared
- ✅ Database migrations (production only)
- ✅ Services restarted (PHP-FPM, Nginx)
- ✅ Deployment tested automatically

## 🚨 Production Deployment Safety

⚠️ **CRITICAL REMINDERS:**
- Production deployments affect LIVE users
- Always test thoroughly in staging first
- Monitor closely after production deployment
- Have rollback plan ready
- Deploy during low-traffic periods

## 🔍 Monitoring
- **GitHub Actions**: `Repository → Actions tab`
- **Server Logs**: SSH to server and check `/var/log/nginx/` and Laravel logs
- **Application Monitoring**: Monitor response times and error rates

---
**💡 Tip**: Use staging environment for testing, production for releases only.