# Deployment Guidelines - Tamam Admin Backend

## 🚨 Critical: Always Validate Before Deployment

**NEVER deploy to production or staging without running the validation script first.**

```bash
./scripts/deployment/validate-deployment.sh
```

### Emergency Fix - Rental Module Issue (Sep 12, 2025)

**What Happened:**
- Removed rental module in two commits but missed the actual active module
- Only removed `Modules/Modules/Rental` (inactive copy)  
- Left `Modules/Rental` (active module) with 150+ routes pointing to deleted controllers
- Both production and staging servers failed immediately

**Root Cause:**
- Incomplete understanding of module structure (two rental directories)
- No deployment validation process
- Direct deployment to production without testing

**Emergency Fix Applied:**
- Removed remaining `Modules/Rental` directory completely
- Cleaned up rental route references in `routes/admin.php`
- All rental routes eliminated (150+ → 0)
- Server functionality restored

## Deployment Safety Measures (Now Implemented)

### 1. Pre-Commit Hooks
- **File:** `.githooks/pre-commit`
- **Purpose:** Catches breaking changes before they reach the repository
- **Tests:** Laravel boot, route compilation, config caching
- **Setup:** Already enabled via `git config core.hooksPath .githooks`

### 2. Deployment Validation Script
- **File:** `scripts/deployment/validate-deployment.sh`
- **Purpose:** Comprehensive validation before deployment
- **Tests:** 
  - Laravel application boot
  - Route compilation and validation
  - Configuration caching
  - Database connectivity
  - Module dependencies
  - Directory permissions

### 3. Automatic Module Seeding (NEW - Sep 13, 2025)

**Issue Fixed:**
- Admin and rental modules were not being activated by default
- Required manual intervention after each deployment
- Modules missing from database even when addons were published

**Solution Implemented:**
- Created `ModuleSeeder.php` to automatically create default modules
- Updated deployment scripts to run `php artisan db:seed --class=ModuleSeeder --force`
- Added GitHub Actions workflow for automated deployment
- Modules now created automatically: admin, rental, food, grocery, pharmacy, ecommerce, parcel

**Files Modified:**
- `database/seeders/ModuleSeeder.php` - NEW
- `database/seeders/DatabaseSeeder.php` - Updated
- `deploy-to-staging.sh` - Added module seeding step
- `deploy-to-production.sh` - Added module seeding step
- `.github/workflows/deploy.yml` - NEW GitHub Actions workflow

### 4. Mandatory Deployment Process

**Before ANY deployment:**
```bash
# 1. Run validation script
./scripts/deployment/validate-deployment.sh

# 2. Only deploy if validation passes with:
# ✅ DEPLOYMENT VALIDATION PASSED - Application is ready for deployment
# Safe to deploy to production/staging servers

# 3. If validation fails with issues, DO NOT DEPLOY:
# ❌ DEPLOYMENT VALIDATION FAILED - Application has critical issues
# DO NOT deploy to production/staging servers
```

## Module Management Best Practices

### When Removing Modules:
1. **Identify all module locations:** Check both `Modules/` and `Modules/Modules/` directories
2. **Check route registrations:** Search for route references in `routes/` directory
3. **Verify controller dependencies:** Search codebase for controller references
4. **Test locally:** Run validation script after changes
5. **Clear all caches:** `php artisan route:clear && php artisan config:clear && php artisan cache:clear`
6. **Test route list:** `php artisan route:list` should not show errors

### Module Verification Commands:
```bash
# List all modules
ls -la Modules/

# Check for remaining references
grep -r "ModuleName" routes/
grep -r "ModuleName" app/

# Validate application
php artisan route:list
php artisan config:cache
./scripts/deployment/validate-deployment.sh
```

## Daily Development Workflow

### For Every Code Change:
1. Make changes
2. Test locally
3. Run validation: `./scripts/deployment/validate-deployment.sh`
4. Commit (pre-commit hook runs automatically)
5. Push to repository
6. Deploy only after validation passes

### For Module Changes:
1. Follow module management best practices above
2. **ALWAYS** test with validation script
3. Check for missing routes or controllers
4. Verify all caches can be built
5. Test actual Laravel server startup

## Emergency Recovery Process

If deployment breaks servers:

### 1. Immediate Response:
```bash
# Identify the breaking commit
git log --oneline -10

# Revert to last working state
git revert <breaking-commit-hash>

# Run validation
./scripts/deployment/validate-deployment.sh

# Push fix immediately
git push origin main
```

### 2. Server Access:
- SSH keys available for both production and staging
- DigitalOcean CLI configured
- Deploy hotfixes immediately after validation

### 3. Root Cause Analysis:
- Document what broke and why
- Update safety measures if needed
- Add lessons learned to this document

## Server Information

- **Production Server:** Accessible via SSH and DigitalOcean CLI
- **Staging Server:** Accessible via SSH and DigitalOcean CLI  
- **Deployment:** Manual deployment process (to be automated with validation)

## Prevention Measures Summary

✅ **Pre-commit hooks** - Catch issues before repository
✅ **Deployment validation script** - Comprehensive testing  
✅ **Documentation** - Clear process and guidelines
✅ **Emergency procedures** - Fast recovery process
🔄 **Future:** Automated deployment with validation gates

**Remember: The validation script is your safety net. Use it religiously.**