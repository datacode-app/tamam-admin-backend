#!/bin/bash

# Production Deployment Checklist for Tamam Admin Backend
# Run this script before any production deployment

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
BOLD='\033[1m'
NC='\033[0m' # No Color

ERRORS=0
WARNINGS=0

# Function to check item
check_item() {
    local description="$1"
    local status="$2"
    
    if [ "$status" = "pass" ]; then
        echo -e "  ✅ ${GREEN}$description${NC}"
    elif [ "$status" = "fail" ]; then
        echo -e "  ❌ ${RED}$description${NC}"
        ((ERRORS++))
    elif [ "$status" = "warn" ]; then
        echo -e "  ⚠️  ${YELLOW}$description${NC}"
        ((WARNINGS++))
    else
        echo -e "  ℹ️  ${BLUE}$description${NC}"
    fi
}

# Header
echo -e "${BOLD}${BLUE}🚀 Tamam Production Deployment Checklist${NC}"
echo -e "${BLUE}=================================================${NC}"
echo ""

# 1. Environment Configuration Check
echo -e "${BOLD}1. Environment Configuration${NC}"

if [ -f .env ]; then
    APP_ENV=$(grep "APP_ENV=" .env | cut -d'=' -f2)
    APP_DEBUG=$(grep "APP_DEBUG=" .env | cut -d'=' -f2)
    APP_URL=$(grep "APP_URL=" .env | cut -d'=' -f2)
    
    if [ "$APP_ENV" = "production" ]; then
        check_item "APP_ENV is set to production" "pass"
    else
        check_item "APP_ENV is NOT production (current: $APP_ENV)" "fail"
    fi
    
    if [ "$APP_DEBUG" = "false" ]; then
        check_item "APP_DEBUG is disabled" "pass"
    else
        check_item "APP_DEBUG is enabled (security risk!)" "fail"
    fi
    
    if [[ "$APP_URL" == *"tamam.shop"* ]]; then
        check_item "APP_URL points to production domain" "pass"
    else
        check_item "APP_URL may not be production URL: $APP_URL" "warn"
    fi
else
    check_item ".env file exists" "fail"
fi

echo ""

# 2. Security Checks
echo -e "${BOLD}2. Security Configuration${NC}"

if [ -f .env ]; then
    # Check for default/weak credentials
    if grep -q "password" .env && grep -q "root" .env; then
        check_item "Database credentials appear to be production-ready" "warn"
    fi
    
    # Check APP_KEY
    APP_KEY=$(grep "APP_KEY=" .env | cut -d'=' -f2)
    if [ -n "$APP_KEY" ] && [ "$APP_KEY" != "base64:" ]; then
        check_item "APP_KEY is configured" "pass"
    else
        check_item "APP_KEY is missing or invalid" "fail"
    fi
    
    # Check for test/debug values
    if grep -q "test" .env || grep -q "debug" .env; then
        check_item "No test/debug values in production .env" "warn"
    else
        check_item "No obvious test values found" "pass"
    fi
fi

echo ""

# 3. Code Quality Checks
echo -e "${BOLD}3. Code Quality & Dependencies${NC}"

# Check for the fix we just applied
if grep -q "images_full_url ?? \[\]" app/CentralLogics/helpers.php; then
    check_item "Order details null-safety fix is applied" "pass"
else
    check_item "Order details null-safety fix is missing" "fail"
fi

# Check for composer dependencies
if [ -f composer.lock ]; then
    check_item "Composer dependencies are locked" "pass"
else
    check_item "composer.lock file missing" "fail"
fi

# Check for any debug statements
if find app/ -name "*.php" -exec grep -l "dd(\|dump(\|var_dump\|print_r" {} \; | head -1 | grep -q "."; then
    check_item "Debug statements found in code" "fail"
else
    check_item "No debug statements found" "pass"
fi

echo ""

# 4. Database & Storage
echo -e "${BOLD}4. Database & Storage${NC}"

# Test database connection
if php artisan tinker --execute="echo 'DB Test: ' . (DB::connection()->getPdo() ? 'Connected' : 'Failed');" 2>/dev/null | grep -q "Connected"; then
    check_item "Database connection successful" "pass"
else
    check_item "Database connection failed" "fail"
fi

# Check migrations
if [ -d database/migrations ]; then
    migration_count=$(ls database/migrations/*.php 2>/dev/null | wc -l)
    check_item "$migration_count database migrations found" "info"
else
    check_item "Database migrations directory not found" "warn"
fi

# Check storage permissions
if [ -d storage ]; then
    if [ -w storage/logs ] && [ -w storage/framework ]; then
        check_item "Storage directories are writable" "pass"
    else
        check_item "Storage directories may need permission fixes" "warn"
    fi
fi

echo ""

# 5. Performance & Optimization
echo -e "${BOLD}5. Performance & Optimization${NC}"

# Check for cached config
if [ -f bootstrap/cache/config.php ]; then
    check_item "Configuration is cached" "pass"
else
    check_item "Configuration not cached (run: php artisan config:cache)" "warn"
fi

# Check for cached routes
if [ -f bootstrap/cache/routes-v7.php ]; then
    check_item "Routes are cached" "pass"
else
    check_item "Routes not cached (run: php artisan route:cache)" "warn"
fi

echo ""

# 6. Critical API Endpoints
echo -e "${BOLD}6. Critical API Endpoints Test${NC}"

# Test the fixed endpoint (if server is running)
if curl -s http://localhost:8000/api/v1/config >/dev/null 2>&1; then
    check_item "Laravel server is responding" "pass"
    
    # Test vendor endpoint structure (without actual order)
    if grep -q "get_order_details" app/Http/Controllers/Api/V1/Vendor/VendorController.php; then
        check_item "Vendor order-details endpoint exists" "pass"
    else
        check_item "Vendor order-details endpoint missing" "fail"
    fi
else
    check_item "Laravel server not running locally" "info"
fi

echo ""

# 7. Mobile App Configuration
echo -e "${BOLD}7. Mobile App Configuration${NC}"

# Check Flutter app constants for production URLs
flutter_apps=("../TamamShop" "../TamamBusiness" "../TamamDriver")
for app in "${flutter_apps[@]}"; do
    if [ -d "$app" ]; then
        app_name=$(basename "$app")
        if [ -f "$app/lib/util/app_constants.dart" ]; then
            if grep -q "admin.tamam.shop" "$app/lib/util/app_constants.dart"; then
                check_item "$app_name points to production API" "pass"
            else
                check_item "$app_name may not point to production API" "warn"
            fi
        else
            check_item "$app_name app_constants.dart not found" "warn"
        fi
    fi
done

echo ""

# Summary
echo -e "${BOLD}${BLUE}📋 Checklist Summary${NC}"
echo -e "${BLUE}===================${NC}"

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo -e "  ${GREEN}✅ All checks passed! Ready for production deployment.${NC}"
elif [ $ERRORS -eq 0 ]; then
    echo -e "  ${YELLOW}⚠️  $WARNINGS warnings found. Review before deployment.${NC}"
else
    echo -e "  ${RED}❌ $ERRORS critical issues found. Fix before deployment!${NC}"
fi

echo -e "  ${BLUE}Warnings: $WARNINGS${NC}"
echo -e "  ${RED}Errors: $ERRORS${NC}"

echo ""

# Final reminders
echo -e "${BOLD}${YELLOW}🔔 Pre-Deployment Reminders:${NC}"
echo "  1. Run full test suite if available"
echo "  2. Backup production database"
echo "  3. Have rollback plan ready"
echo "  4. Monitor logs after deployment"
echo "  5. Test critical user flows"
echo "  6. Verify mobile apps can connect"

echo ""

if [ $ERRORS -gt 0 ]; then
    echo -e "${RED}❌ DEPLOYMENT NOT RECOMMENDED - Fix errors first!${NC}"
    exit 1
else
    echo -e "${GREEN}✅ Pre-deployment checks completed${NC}"
    exit 0
fi