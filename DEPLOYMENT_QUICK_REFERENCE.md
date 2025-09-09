# 🚀 Deployment Quick Reference

## ⚡ Auto-Deployment (Recommended)
```bash
git add .
git commit -m "your changes"
git push origin main  # 🎯 Triggers automatic deployment
```
**Result**: GitHub Actions automatically deploys to staging.tamam.shop

## 🔧 Manual Deployment
```bash
./deploy-to-staging.sh  # Direct deployment script
```

## 🔑 Setup GitHub Secrets

### 🚀 Automated (Recommended)
```bash
./setup-github-secrets.sh  # One-command setup
```

### ⚡ Manual GitHub CLI
```bash
gh secret set STAGING_SSH_KEY < ~/.ssh/tamam_staging_key
echo "46.101.190.171" | gh secret set STAGING_SERVER_IP
```

### Required Secrets
| Secret Name | Value | Description |
|-------------|-------|-------------|
| `STAGING_SSH_KEY` | Content of `~/.ssh/tamam_staging_key` | SSH private key for server access |
| `STAGING_SERVER_IP` | `46.101.190.171` | Staging server IP address |

## 📍 URLs
- **Admin Panel**: https://staging.tamam.shop/admin
- **Login Page**: https://staging.tamam.shop/login/admin
- **GitHub Actions**: `Repository → Actions tab`

## 🧪 Testing After Deployment
```bash
# Test key routes
curl -I https://staging.tamam.shop/admin        # Should return 302
curl -I https://staging.tamam.shop/login/admin  # Should return 200
curl -I https://staging.tamam.shop/admin/addon  # Should return 302
```

## 📊 What Gets Deployed
- ✅ Laravel application files (app/, routes/, config/, etc.)
- ✅ Composer dependencies updated
- ✅ File permissions fixed
- ✅ Laravel caches cleared
- ✅ Services restarted (PHP-FPM, Nginx)
- ✅ Deployment tested automatically

---
**💡 Tip**: Monitor deployments in GitHub Actions tab to see real-time progress and logs.