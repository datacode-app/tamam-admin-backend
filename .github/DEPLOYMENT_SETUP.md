# 🚀 Automated Staging Deployment Setup

This guide explains how to set up automatic deployment to staging server on every push to the main branch.

## 📋 Prerequisites

1. GitHub repository with admin access
2. Staging server SSH key (`~/.ssh/tamam_staging_key`)
3. Staging server IP: `46.101.190.171`

## 🔧 GitHub Secrets Setup

### Method 1: Automated Setup with GitHub CLI (Recommended) 🚀

```bash
# 1. Install GitHub CLI (if not installed)
brew install gh

# 2. Authenticate with GitHub
gh auth login

# 3. Run the automated setup script
./setup-github-secrets.sh
```

**That's it!** The script automatically:
- ✅ Reads your SSH key from `~/.ssh/tamam_staging_key`
- ✅ Sets up both required secrets
- ✅ Verifies everything is configured correctly

### Method 2: Manual Setup via GitHub Web Interface

1. **Go to Repository Settings**:
   - Navigate to your GitHub repository
   - Click "Settings" tab
   - Go to "Secrets and variables" → "Actions"

2. **Add Required Secrets**:

   #### `STAGING_SSH_KEY`
   ```bash
   # Copy your SSH private key content
   cat ~/.ssh/tamam_staging_key
   ```
   - Copy the entire content (including `-----BEGIN OPENSSH PRIVATE KEY-----` and `-----END OPENSSH PRIVATE KEY-----`)
   - Paste into GitHub secret named `STAGING_SSH_KEY`

   #### `STAGING_SERVER_IP`
   ```
   46.101.190.171
   ```

### Method 3: GitHub CLI Commands (Individual)

```bash
# Set SSH key secret
gh secret set STAGING_SSH_KEY < ~/.ssh/tamam_staging_key

# Set server IP secret
echo "46.101.190.171" | gh secret set STAGING_SERVER_IP

# Verify secrets were set
gh secret list
```

### 🔐 Security Notes

- SSH key is encrypted and only accessible during workflow execution
- Secrets are never logged or exposed in workflow output
- Only repository collaborators can view/edit secrets

## 🎯 How It Works

### Trigger Conditions
- ✅ **Automatic**: Triggers on every push to `main` or `master` branch
- ✅ **Manual**: Can be triggered manually via GitHub Actions tab

### Deployment Steps
1. **📥 Checkout Code**: Downloads latest repository code
2. **🔐 Setup SSH**: Configures SSH access to staging server
3. **🧪 Test Connection**: Verifies server connectivity
4. **📦 Deploy Files**: Syncs changed files using rsync
5. **🔧 Set Permissions**: Ensures proper Laravel file permissions
6. **📚 Update Dependencies**: Runs composer install
7. **🧹 Clear Caches**: Clears Laravel caches
8. **🔄 Restart Services**: Restarts PHP-FPM and Nginx
9. **🧪 Test Deployment**: Verifies all routes are working

### Success Verification
- Tests admin dashboard, login, and addon routes
- Ensures all return proper HTTP status codes (200 or 302)
- Fails deployment if critical routes don't respond

## 📊 Monitoring Deployments

### GitHub Actions Dashboard
- Go to "Actions" tab in your repository
- View deployment status, logs, and history
- Get notified of deployment success/failure

### Deployment URL
- **Staging Admin**: https://staging.tamam.shop/admin
- **Admin Login**: https://staging.tamam.shop/login/admin

## 🎮 Manual Deployment

You can still use the local script when needed:
```bash
./deploy-to-staging.sh
```

## 🚨 Troubleshooting

### Common Issues

1. **SSH Connection Failed**
   - Verify `STAGING_SSH_KEY` secret contains correct private key
   - Ensure SSH key has access to the staging server

2. **Permission Denied**
   - Check if SSH key is properly formatted
   - Verify server IP in `STAGING_SERVER_IP` secret

3. **Composer Install Failed**
   - Server might be out of disk space
   - Check PHP/Composer version compatibility

4. **Route Test Failed**
   - Check Laravel logs on server: `/var/www/tamam/storage/logs/laravel.log`
   - Verify database connectivity
   - Ensure .env file is properly configured

### Debug Commands (Run on Server)
```bash
# Check Laravel status
ssh -i ~/.ssh/tamam_staging_key root@46.101.190.171 "cd /var/www/tamam && php artisan route:list | head -5"

# Check logs
ssh -i ~/.ssh/tamam_staging_key root@46.101.190.171 "tail -20 /var/www/tamam/storage/logs/laravel.log"

# Check services
ssh -i ~/.ssh/tamam_staging_key root@46.101.190.171 "systemctl status nginx php8.3-fpm"
```

## 🎉 Benefits

- **Automatic Deployment**: No manual steps required
- **Consistent Process**: Same deployment process every time  
- **Fast Deployment**: Only changed files are transferred
- **Failure Detection**: Automatically tests deployment success
- **Rollback Capability**: Git history allows easy rollbacks
- **Team Collaboration**: All team members deploy automatically

---

**Next Steps**: Set up the GitHub secrets and push to main branch to trigger your first automated deployment!