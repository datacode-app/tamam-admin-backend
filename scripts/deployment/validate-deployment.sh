#!/bin/bash

# =============================================================================
# DEPLOYMENT VALIDATION SCRIPT
# =============================================================================
# This script validates that the application can start successfully before 
# deployment to prevent server failures due to missing dependencies or routes
# 
# Usage: ./scripts/deployment/validate-deployment.sh
# Exit codes: 0 = success, 1 = validation failed
# =============================================================================

set -e  # Exit on any error

echo "🔍 Starting deployment validation..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[VALIDATION]${NC} $1"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Validation checks
VALIDATION_FAILED=0

print_status "Checking Laravel application..."

# 1. Check if Laravel can boot without errors
print_status "Testing Laravel application boot..."
if php artisan --version > /dev/null 2>&1; then
    print_success "Laravel application boots successfully"
else
    print_error "Laravel application failed to boot"
    VALIDATION_FAILED=1
fi

# 2. Clear and test caches
print_status "Clearing and testing caches..."
php artisan config:clear > /dev/null 2>&1
php artisan route:clear > /dev/null 2>&1
php artisan cache:clear > /dev/null 2>&1

if php artisan config:cache > /dev/null 2>&1; then
    print_success "Configuration cache builds successfully"
    php artisan config:clear > /dev/null 2>&1  # Clear it again
else
    print_error "Configuration cache build failed"
    VALIDATION_FAILED=1
fi

if php artisan route:cache > /dev/null 2>&1; then
    print_success "Route cache builds successfully" 
    php artisan route:clear > /dev/null 2>&1  # Clear it again
else
    print_error "Route cache build failed"
    VALIDATION_FAILED=1
fi

# 3. Check route list for broken routes
print_status "Checking for broken routes..."
ROUTE_OUTPUT=$(php artisan route:list 2>&1)
if echo "$ROUTE_OUTPUT" | grep -q "Class.*not found\|does not exist\|ReflectionException"; then
    print_error "Found broken routes with missing controllers"
    print_error "Route validation output:"
    echo "$ROUTE_OUTPUT" | grep -E "Class.*not found|does not exist|ReflectionException" || true
    VALIDATION_FAILED=1
else
    ROUTE_COUNT=$(echo "$ROUTE_OUTPUT" | wc -l)
    print_success "All routes valid ($ROUTE_COUNT routes found)"
fi

# 4. Test database connection (if configured)
print_status "Testing database connection..."
if php artisan db:show > /dev/null 2>&1; then
    print_success "Database connection successful"
else
    print_warning "Database connection test skipped (not configured or failed)"
fi

# 5. Check for missing module dependencies
print_status "Checking module dependencies..."
if [ -d "Modules" ]; then
    MISSING_MODULES=0
    for module_dir in Modules/*/; do
        if [ -d "$module_dir" ]; then
            module_name=$(basename "$module_dir")
            if [ ! -f "${module_dir}module.json" ] && [ ! -f "${module_dir}composer.json" ]; then
                print_warning "Module $module_name appears incomplete (missing module.json/composer.json)"
                MISSING_MODULES=$((MISSING_MODULES + 1))
            fi
        fi
    done
    
    if [ $MISSING_MODULES -eq 0 ]; then
        print_success "All modules appear complete"
    else
        print_warning "$MISSING_MODULES modules may have issues"
    fi
else
    print_status "No modules directory found"
fi

# 6. Check critical directories and permissions
print_status "Checking critical directories and permissions..."

REQUIRED_DIRS=("storage/logs" "storage/framework" "bootstrap/cache")
for dir in "${REQUIRED_DIRS[@]}"; do
    if [ ! -d "$dir" ]; then
        print_error "Required directory missing: $dir"
        VALIDATION_FAILED=1
    elif [ ! -w "$dir" ]; then
        print_error "Directory not writable: $dir"
        VALIDATION_FAILED=1
    fi
done

if [ $VALIDATION_FAILED -eq 0 ]; then
    print_success "All directory permissions are correct"
fi

# Final validation result
echo ""
if [ $VALIDATION_FAILED -eq 0 ]; then
    print_success "🎉 DEPLOYMENT VALIDATION PASSED - Application is ready for deployment"
    echo -e "${GREEN}Safe to deploy to production/staging servers${NC}"
    exit 0
else
    print_error "🚨 DEPLOYMENT VALIDATION FAILED - Application has critical issues"
    echo -e "${RED}DO NOT deploy to production/staging servers${NC}"
    echo -e "${YELLOW}Fix the above issues before deployment${NC}"
    exit 1
fi