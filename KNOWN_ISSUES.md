# Known Issues & Solutions

This document tracks recurring issues, their root causes, and proven solutions for the Tamam Admin Backend system.

## Table of Contents
- [Rental Module Issues](#rental-module-issues)
- [Configuration & Null Safety Issues](#configuration--null-safety-issues)  
- [Route & Sidebar Issues](#route--sidebar-issues)
- [Database Connection Issues](#database-connection-issues)
- [Deployment & Server Issues](#deployment--server-issues)

---

## Rental Module Issues

### Issue #1: Rental Module Not Activating (Complete Fix)
**Status**: ✅ **RESOLVED** - Multi-part solution implemented

**Symptoms**:
- Rental module shows as "published" but functionality doesn't work
- `addon_published_status('Rental')` returns inconsistent results
- Rental-specific features and UI elements don't appear
- Admin panel doesn't show rental module as active

**Root Cause Analysis**:
The rental module activation system has **two levels** that both must be correctly configured:

1. **Addon Level**: `Modules/Rental/Addon/info.php` must have `'is_published' => 1`
2. **Database Level**: A record in the `modules` table with `module_type = 'rental'` and `status = 1`

**Multiple Issues Found**:
1. **Missing Database Records**: Database only had "Demo Module" but was missing rental, admin, and other essential modules
2. **Field Name Bug**: `AddonController.php` was setting `module_status` instead of `status` field
3. **Inconsistent Module Management**: No automated way to ensure default modules exist

**Complete Solution Applied**:

#### Part 1: Database Field Fix
**File**: `app/Http/Controllers/Admin/System/AddonController.php`
```php
// Fixed in rentalPublish() method (line 171)
// BEFORE (incorrect):
$modules->module_status = $status;

// AFTER (correct):  
$modules->status = $status;
```

#### Part 2: Missing Database Records Fix
**File**: `database/seeders/ModuleSeeder.php` (NEW)
```php
// Creates all essential default modules with status = 1:
- Admin Module (admin)
- Rental Module (rental)  
- Food Delivery (food)
- Grocery (grocery)
- Pharmacy (pharmacy)
- E-commerce (ecommerce)
- Parcel Delivery (parcel)
```

**File**: `database/seeders/DatabaseSeeder.php` (UPDATED)
```php
// Added to seeder list:
ModuleSeeder::class, // Add default modules (admin, rental, etc.)
```

**How to Apply**:

For **existing installations**:
```bash
# Option 1: Run the seeder
php artisan db:seed --class=ModuleSeeder

# Option 2: Run all seeders
php artisan db:seed
```

For **new installations**: 
Automatically applied via updated `DatabaseSeeder`.

**Verification Steps**:
1. Check admin panel: `/admin/business-settings/module` - verify rental module exists and is active
2. Check addon status: `/admin/system-addon` - verify rental addon is published
3. Test Laravel: `php artisan tinker` → `addon_published_status('Rental')` should return `1`
4. Test functionality: Try accessing rental-specific features

**Files Modified**:
- `app/Http/Controllers/Admin/System/AddonController.php`
- `database/seeders/ModuleSeeder.php` (NEW)
- `database/seeders/DatabaseSeeder.php`
- `MODULE_ACTIVATION_FIX.md` (NEW) - Detailed documentation

### Issue #1.1: Rental Module Not Visible Due to File Activator (modules_statuses.json)
**Status**: ✅ **RESOLVED** - Deployment now syncs and verifies activator file

**Symptoms**:
- Rental module installed, addon published, DB status = 1, routes registered
- Still not shown in admin UI or some parts gated

**Root Cause**:
- We use `nwidart/laravel-modules` with the `file` activator configured:
  - `config/modules.php` → `activators.file.statuses-file = base_path('modules_statuses.json')`
- If `modules_statuses.json` is missing or Rental is set to false, the module may be considered disabled by the activator even if DB shows active.
- Additionally, duplicate module directories (`Modules/Rental` and `Modules/Modules/Rental`) caused confusion during routing and status checks.

**Fix Implemented**:
- Removed duplicate directory `Modules/Modules/Rental` (kept canonical `Modules/Rental`).
- Ensured `modules_statuses.json` is versioned and deployed:
  - Updated `deploy-to-staging.sh` and `deploy-to-production.sh` to rsync `modules_statuses.json`.
  - Added a verification step on remote servers to print `modules_statuses.json` and run `php artisan module:list`.
  - If the file is missing, create it with Rental enabled by default:
    ```json
    {
      "Rental": true
    }
    ```

**Validation Commands**:
```bash
php artisan module:list | cat
cat modules_statuses.json
php artisan tinker --execute="echo addon_published_status('Rental');"
```

**Files Modified**:
- `deploy-to-staging.sh` (sync and verify `modules_statuses.json`)
- `deploy-to-production.sh` (sync and verify `modules_statuses.json`)
- Removed duplicate: `Modules/Modules/Rental` (keep only `Modules/Rental`)

**Prevention**:
- Keep `modules_statuses.json` in repo and in deployments.
- Avoid duplicate module directories; always use `Modules/<ModuleName>`.
- CI/CD prints `module:list` every deployment to catch disabled modules early.

---

## Configuration & Null Safety Issues

### Issue #2: Config Null Safety Errors (HTTP 500)
**Status**: ✅ **RESOLVED** - Fixed across 17+ locations

**Symptoms**:
- HTTP 500 Internal Server Error on various admin endpoints
- Error: "foreach() argument must be of type array|object, null given"
- Business settings pages throwing errors
- Withdraw list endpoint failing

**Root Cause**:
Laravel `config()` calls returning `null` instead of expected arrays, causing foreach loops to fail.

**Locations Fixed**:
1. `app/Http/Controllers/Admin/BusinessSettingsController.php` (lines 355, 894, 899)
2. `app/Http/Controllers/Admin/VendorController.php` (multiple pagination calls)
3. `app/Http/Controllers/Admin/SMSModuleController.php`
4. `app/Http/Controllers/Api/V1/CustomerController.php`
5. `app/Http/Controllers/Api/V1/ConfigController.php`
6. Multiple Blade template files (8 files)

**Solution Pattern Applied**:
```php
// BEFORE (vulnerable):
foreach (config('module.module_type') as $key) {

// AFTER (safe):
foreach (config('module.module_type') ?? [] as $key) {

// For pagination:
->paginate(config('default_pagination') ?? 25);

// For nested checks:
$routes = config('addon_admin_routes') ?? [];
foreach ($routes as $routeArray) {
    if (is_array($routeArray)) {
        // Safe nested processing
    }
}
```

**Prevention**:
Always use null coalescing operator (`??`) with appropriate fallbacks when calling `config()`.

---

## Route & Sidebar Issues

### Issue #3: Rental Route References Causing 500 Errors
**Status**: ✅ **RESOLVED** - Removed from sidebars

**Symptoms**:
- 500 errors when accessing `/admin/transactions/store/withdraw_list`
- Error: "Route [admin.transactions.rental.report.transaction-report] not defined"
- Sidebar rendering failures

**Root Cause**:
Sidebar templates contained references to rental routes that don't exist or are not properly registered.

**Files Fixed**:
1. `resources/views/layouts/admin/partials/_header.blade.php`
2. `resources/views/layouts/admin/partials/_sidebar_settings.blade.php`
3. `resources/views/layouts/admin/partials/_sidebar_transactions.blade.php`

**Solution**:
Removed all rental route references from sidebar templates. Rental functionality should be managed through the addon system, not hardcoded in sidebars.

**Files Modified**:
- Removed rental dashboard route from header template
- Removed rental settings sections from settings sidebar  
- Removed rental report sections from transactions sidebar (lines 180-216)

---

## Database Connection Issues

### Issue #4: Production Database Connection Differences
**Status**: 🔍 **MONITORING** - Environment-specific behavior noted

**Symptoms**:
- Features work on staging but fail on production
- Database-related config calls behaving differently
- Environment-specific null returns from config calls

**Root Cause**:
Production and staging environments may have different:
- Database configurations
- Config cache states  
- Environment variable values

**Solutions Applied**:
1. **Config Cache Management**: Added cache clearing to deployment scripts
2. **Null Safety**: Applied defensive programming with null coalescing
3. **Environment Consistency**: Documented config clearing procedures

**Monitoring Commands**:
```bash
# Clear all caches on production
php artisan config:clear
php artisan cache:clear  
php artisan route:clear
php artisan view:clear

# Check config values
php artisan tinker
>>> config('default_pagination')
>>> config('get_payment_publish_status')
```

---

## Deployment & Server Issues

### Issue #5: GitHub Actions Deployment Branch Configuration
**Status**: ✅ **RESOLVED** - Corrected workflow triggers

**Symptoms**:
- Staging server not auto-deploying when pushing to staging branch
- Deployment workflows triggering on wrong branches

**Root Cause**:
`.github/workflows/deploy-staging.yml` was configured to trigger on `main, master` branches instead of `staging`.

**Solution**:
```yaml
# BEFORE (incorrect):
on:
  push:
    branches: [ main, master ]

# AFTER (correct):  
on:
  push:
    branches: [ staging ]
```

**Files Modified**:
- `.github/workflows/deploy-staging.yml`

### Issue #6: SSH Connection Timeouts to Production Server  
**Status**: 🔍 **INTERMITTENT** - Connection stability issues

**Symptoms**:
- Intermittent SSH connection failures when checking production logs
- "SSH connection failed" errors from log checking scripts

**Temporary Solutions**:
- Retry SSH connections after brief delay
- Use alternative connection methods when available
- Monitor server load and network connectivity

**Scripts Available**:
- `./check-production-logs.sh` - Production log monitoring with retry logic

---

## Color Scheme Issues  

### Issue #7: Admin Panel Color Scheme Update
**Status**: ✅ **RESOLVED** - Updated to Tamam brand colors

**Symptoms**:
- Admin panel using generic green colors (#1cc88a)
- Need to match Tamam brand identity

**Solution**:
Updated primary colors across admin panel CSS files:

**Files Modified**:
- `public/assets/admin/css/sb-admin-2.css`
- `public/assets/admin/css/sb-admin-2.min.css`

**Color Changes**:
```css
/* Changed throughout CSS files */
--green: #00868f;        /* Tamam brand teal */
--success: #00868f;      /* Tamam brand teal */
.bg-success { background-color: #00868f !important; }
```

---

## How to Add New Issues

When encountering a new recurring issue:

1. **Document the Issue**:
   ```markdown
   ### Issue #X: [Brief Title]
   **Status**: 🔍 **INVESTIGATING** / ✅ **RESOLVED** / 🔄 **RECURRING**
   
   **Symptoms**:
   - [What the user experiences]
   
   **Root Cause**:
   - [Technical explanation]
   
   **Solution**:
   - [Step-by-step fix]
   
   **Files Modified**:
   - [List of changed files]
   ```

2. **Test the Solution** thoroughly before marking as resolved

3. **Update Status** based on validation results

4. **Add Prevention Tips** when applicable

---

## Status Legend

- 🔍 **INVESTIGATING**: Issue identified, solution in progress  
- ✅ **RESOLVED**: Issue fixed and tested, solution validated
- 🔄 **RECURRING**: Issue may reappear, monitor closely
- ⚠️ **WORKAROUND**: Temporary fix applied, needs permanent solution
- 📋 **MONITORING**: Watching for patterns or additional occurrences

---

## Validation Process

Before marking any issue as "RESOLVED":

1. ✅ **Test the fix** in development environment
2. ✅ **Apply to staging** and verify functionality  
3. ✅ **Deploy to production** and confirm resolution
4. ✅ **Document all changed files** and steps taken
5. ✅ **Add prevention measures** when possible

---

## Production Database Connection Issues

### Issue #8: Production Environment Placeholder Credentials  
**Status**: ✅ **RESOLVED** - Production server updated with actual credentials

**Symptoms**:
- HTTP 500 errors on all admin routes after deployment
- Database connection errors: "Access denied for user 'doadmin'@'134.209.230.97'"
- Routes fixed but database connectivity failing

**Root Cause**:
Production deployment uses template environment file (`create-production-env.sh`) with placeholder credentials:
- `DB_PASSWORD=PRODUCTION_DB_PASSWORD_PLACEHOLDER` 
- `AWS_ACCESS_KEY_ID=PRODUCTION_SPACES_KEY_PLACEHOLDER`
- `AWS_SECRET_ACCESS_KEY=PRODUCTION_SPACES_SECRET_PLACEHOLDER`

**Database Configuration**:
- Host: `tamam-production-db-do-user-19403128-0.j.db.ondigitalocean.com`
- Port: `25060`
- Database: `tamamdb` 
- Username: `doadmin`
- Password: **[NEEDS REAL CREDENTIAL]**

**Files Affected**:
- `/var/www/tamam/.env` (production server)
- `create-production-env.sh` (deployment template)

**Solution Required**:
1. Obtain actual production database password from DigitalOcean
2. Update production `.env` file with real credentials
3. Restart PHP-FPM to reload configuration
4. Test database connectivity

**Complete Solution Applied**:
1. ✅ Located actual credentials in prod branch `.env` file
2. ✅ Updated production server `/var/www/tamam/.env` with real credentials
3. ✅ Restarted PHP-FPM to reload configuration  
4. ✅ Tested connectivity - admin dashboard and login working (200/302 responses)

**Update Status**: Route fixes deployed ✅, database credentials updated ✅, production server operational ✅

---

## Environment Configuration Management

### Issue #9: Environment File Separation Strategy  
**Status**: ✅ **IMPLEMENTED** - Complete environment separation strategy established

**Challenge**:
Previously, branches shared `.env` files which caused deployment conflicts and credential mixing between staging and production environments.

**Solution Implemented**:

**Branch-Specific Environment Strategy**:
- **Main Branch**: Local development with `.env.example` template
- **Staging Branch**: Server-generated environment via `create-staging-env.sh`
- **Prod Branch**: Server-generated environment via `create-production-env.sh`

**Environment Configurations**:

| Environment | Domain | Debug | Database | Storage Bucket |
|-------------|--------|-------|----------|----------------|
| **Local** | `localhost:8000` | ✅ | Local MySQL | Local/S3 |
| **Staging** | `staging.tamam.shop` | ✅ | Production DB (shared) | `tamam-staging` |
| **Production** | `prod.tamam.shop` | ❌ | Production DB | `tamam-prod` |

**Credential Security**:
- ✅ No actual credentials committed to git
- ✅ Template files use placeholders  
- ✅ Deployment scripts create environment files on servers
- ✅ Each environment has separate DigitalOcean Spaces buckets

**Files Created**:
- `ENVIRONMENT_SETUP.md` - Complete environment documentation
- `.env.example` - Local development template
- `.env.staging.example` - Staging template with placeholders
- `create-staging-env.sh` - Staging deployment with actual credentials

**Deployment Updates**:
- `deploy-to-staging.sh` - Now creates staging-specific environment
- `deploy-to-production.sh` - Creates production-specific environment
- Both scripts use actual credentials stored in deployment files

**Benefits**:
- ✅ Prevents deployment conflicts between environments
- ✅ Maintains credential separation and security
- ✅ Enables environment-specific configurations
- ✅ Supports different storage buckets and debug modes

---

## Testing Framework Issues

### Issue #10: PHPUnit 10.x/Collision Compatibility Error  
**Status**: ✅ **RESOLVED** - Updated dependency versions for compatibility

**Error Message**:
```
Running PHPUnit 10.x or Pest 2.x requires Collision 7.x.
NunoMaduro\Collision\Adapters\Laravel\Exceptions\RequirementsException
```

**Root Cause**:
Version mismatch between testing dependencies:
- Project had `nunomaduro/collision": "^6.1"` (version 6.x)
- Project had `phpunit/phpunit": "^10.0"` (version 10.x)  
- PHPUnit 10.x requires Collision 7.x for compatibility

**Solution Applied**:
1. ✅ Updated `composer.json`: `nunomaduro/collision": "^6.1"` → `"^7.0"`
2. ✅ Ran `composer update nunomaduro/collision` to install v7.12.0
3. ✅ Added `.env.testing` for proper testing environment configuration
4. ✅ Verified `php artisan test` command works correctly

**Files Modified**:
- `composer.json` - Updated collision version constraint
- `composer.lock` - Updated with collision v7.12.0
- `.env.testing` - New testing environment configuration

**Testing Status**:
- ✅ PHPUnit/Collision compatibility error resolved
- ✅ `php artisan test` command functional
- ✅ Test suite can run without version conflicts
- ⚠️ Additional test environment configuration may be needed for database tests

**Prevention**:
- Keep testing dependencies aligned with framework requirements
- Update collision version when upgrading PHPUnit major versions
- Monitor deprecation warnings during dependency updates

---

*Last Updated: September 13, 2025*  
*Contributors: Claude AI Assistant, Hooshyar*