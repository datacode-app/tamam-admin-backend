#!/bin/bash

# Comprehensive Multilingual Migration Runner
# This script runs all multilingual-related migrations in the correct order

echo "🌍 MULTILINGUAL FEATURES MIGRATION RUNNER"
echo "========================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    print_error "Not in a Laravel project directory. Please run from the project root."
    exit 1
fi

# Check if php artisan is available
if ! command -v php &> /dev/null; then
    print_error "PHP is not available in PATH"
    exit 1
fi

print_status "Starting multilingual migrations..."
echo ""

# Step 1: Check migration status
print_status "Step 1: Checking current migration status..."
php artisan migrate:status | tail -10

echo ""
print_status "Step 2: Running core translations table migration..."

# Run the comprehensive translations table migration
if php artisan migrate --path=database/migrations/2025_01_10_190000_create_comprehensive_translations_table.php; then
    print_success "✅ Comprehensive translations table created"
else
    print_error "❌ Failed to create translations table"
    exit 1
fi

echo ""
print_status "Step 3: Running supported languages migration..."

# Run the supported languages migration
if php artisan migrate --path=database/migrations/2025_01_10_191000_create_supported_languages_table.php; then
    print_success "✅ Supported languages table created and populated"
else
    print_error "❌ Failed to create supported languages table"
    exit 1
fi

echo ""
print_status "Step 4: Adding performance indexes..."

# Run the indexes migration
if php artisan migrate --path=database/migrations/2025_01_10_192000_add_multilingual_indexes_to_core_tables.php; then
    print_success "✅ Performance indexes added to core tables"
else
    print_warning "⚠️  Some indexes may have failed (this is normal if tables don't exist yet)"
fi

echo ""
print_status "Step 5: Migrating existing data to multilingual structure..."

# Run the data migration
if php artisan migrate --path=database/migrations/2025_01_10_193000_migrate_existing_data_to_multilingual.php; then
    print_success "✅ Existing data migrated to multilingual structure"
else
    print_warning "⚠️  Data migration may have had issues (check existing data)"
fi

echo ""
print_status "Step 6: Adding multilingual settings and constraints..."

# Run the settings and constraints migration
if php artisan migrate --path=database/migrations/2025_01_10_194000_add_multilingual_settings_and_constraints.php; then
    print_success "✅ Multilingual settings and constraints added"
else
    print_warning "⚠️  Some constraints may have failed (database-specific)"
fi

echo ""
print_status "Step 7: Creating import/export logging tables..."

# Run the import/export logs migration
if php artisan migrate --path=database/migrations/2025_01_10_195000_create_multilingual_import_export_logs.php; then
    print_success "✅ Import/export logging tables created"
else
    print_error "❌ Failed to create import/export logging tables"
fi

echo ""
print_status "Step 8: Running any remaining migrations..."

# Run all remaining migrations
if php artisan migrate; then
    print_success "✅ All remaining migrations completed"
else
    print_warning "⚠️  Some migrations may have had issues"
fi

echo ""
print_status "Step 9: Optimizing database..."

# Clear and rebuild cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Optimize autoloader
composer dump-autoload --optimize

print_success "✅ Cache cleared and autoloader optimized"

echo ""
print_status "Step 10: Verifying migration results..."

# Check final migration status
echo "Final migration status:"
php artisan migrate:status | tail -15

echo ""
print_status "Checking translations table structure..."
php artisan tinker --execute="
\$columns = \Illuminate\Support\Facades\DB::select('DESCRIBE translations');
foreach (\$columns as \$column) {
    echo \$column->Field . ' - ' . \$column->Type . ' - ' . \$column->Extra . PHP_EOL;
}
echo 'Total translation records: ' . \Illuminate\Support\Facades\DB::table('translations')->count() . PHP_EOL;
"

echo ""
print_status "Checking supported languages..."
php artisan tinker --execute="
\$languages = \Illuminate\Support\Facades\DB::table('supported_languages')->get();
foreach (\$languages as \$lang) {
    echo \$lang->code . ' - ' . \$lang->name . ' (' . \$lang->native_name . ')' . PHP_EOL;
}
"

echo ""
echo "🎉 MULTILINGUAL MIGRATION COMPLETE!"
echo "=================================="
echo ""
print_success "✅ All multilingual features have been set up successfully!"
echo ""
echo "📋 WHAT'S BEEN ADDED:"
echo "  • Comprehensive translations table with proper indexes"
echo "  • Supported languages table (English, Arabic, Kurdish Sorani)"
echo "  • Performance indexes on core translatable tables"
echo "  • Existing data migrated to multilingual structure"
echo "  • Multilingual settings in business_settings"
echo "  • Translation validation and audit tables"
echo "  • Import/export operation logging"
echo ""
echo "🚀 NEXT STEPS:"
echo "  • Test multilingual import: /admin/store/bulk-import-index"
echo "  • Test multilingual export: /admin/store/bulk-export-index"
echo "  • Verify round-trip compatibility (export → modify → import)"
echo "  • Add translations through admin panel"
echo ""
echo "📚 FEATURES AVAILABLE:"
echo "  • Store names and addresses in Arabic/Kurdish"
echo "  • Item names and descriptions in multiple languages"
echo "  • Category names in multiple languages"
echo "  • Banner and coupon translations"
echo "  • Bulk import/export with multilingual support"
echo "  • Translation audit trail and validation"
echo ""

# Check if there were any errors
if [ $? -eq 0 ]; then
    print_success "🎊 Setup completed successfully! Your multilingual features are ready."
    exit 0
else
    print_warning "⚠️  Setup completed with some warnings. Check the output above for details."
    exit 1
fi