#!/bin/bash

# Tamam Migration Manager
# Centralized migration management for all environments

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
BACKUP_DIR="$PROJECT_ROOT/database/backups"
LOG_DIR="$PROJECT_ROOT/storage/logs"
MIGRATION_LOG="$LOG_DIR/migration-$(date +%Y%m%d_%H%M%S).log"

# Ensure directories exist
mkdir -p "$BACKUP_DIR" "$LOG_DIR"

# Logging function
log() {
    echo -e "${GREEN}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$MIGRATION_LOG"
}

error() {
    echo -e "${RED}[$(date '+%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1" | tee -a "$MIGRATION_LOG"
}

warning() {
    echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1" | tee -a "$MIGRATION_LOG"
}

info() {
    echo -e "${BLUE}[$(date '+%Y-%m-%d %H:%M:%S')] INFO:${NC} $1" | tee -a "$MIGRATION_LOG"
}

# Function to check database connection
check_db_connection() {
    log "Checking database connection..."
    if php artisan db:show >/dev/null 2>&1; then
        log "✅ Database connection successful"
        return 0
    else
        error "❌ Database connection failed"
        return 1
    fi
}

# Function to backup database before migrations
backup_database() {
    local backup_name="migration_backup_$(date +%Y%m%d_%H%M%S).sql"
    local backup_path="$BACKUP_DIR/$backup_name"
    
    log "Creating database backup: $backup_name"
    
    # Extract database credentials from .env
    local db_host=$(grep DB_HOST .env | cut -d '=' -f2)
    local db_port=$(grep DB_PORT .env | cut -d '=' -f2)
    local db_database=$(grep DB_DATABASE .env | cut -d '=' -f2)
    local db_username=$(grep DB_USERNAME .env | cut -d '=' -f2)
    local db_password=$(grep DB_PASSWORD .env | cut -d '=' -f2)
    
    if command -v mysqldump &> /dev/null; then
        mysqldump -h"$db_host" -P"$db_port" -u"$db_username" -p"$db_password" "$db_database" > "$backup_path" 2>/dev/null
        if [ $? -eq 0 ]; then
            log "✅ Database backup created: $backup_path"
            echo "$backup_path"
        else
            warning "⚠️  Database backup failed, continuing without backup"
        fi
    else
        warning "⚠️  mysqldump not available, skipping backup"
    fi
}

# Function to validate migration files
validate_migrations() {
    log "Validating migration files..."
    local validation_errors=0
    
    # Check for duplicate migration names
    local duplicates=$(ls database/migrations/*.php | xargs basename -s .php | sort | uniq -d)
    if [ ! -z "$duplicates" ]; then
        error "❌ Duplicate migration names found:"
        echo "$duplicates"
        ((validation_errors++))
    fi
    
    # Check for missing up/down methods
    for migration_file in database/migrations/*.php; do
        if ! grep -q "public function up()" "$migration_file"; then
            error "❌ Missing up() method in: $(basename "$migration_file")"
            ((validation_errors++))
        fi
        if ! grep -q "public function down()" "$migration_file"; then
            error "❌ Missing down() method in: $(basename "$migration_file")"
            ((validation_errors++))
        fi
    done
    
    if [ $validation_errors -eq 0 ]; then
        log "✅ All migration files validated successfully"
        return 0
    else
        error "❌ $validation_errors validation errors found"
        return 1
    fi
}

# Function to run migrations with rollback capability
run_migrations() {
    local environment=${1:-"development"}
    local force_flag=""
    
    if [ "$environment" != "development" ]; then
        force_flag="--force"
    fi
    
    log "Running migrations for environment: $environment"
    
    # Get current migration state
    local pre_migration_count=$(php artisan migrate:status 2>/dev/null | grep -c "Ran" || echo "0")
    
    # Run migrations
    if php artisan migrate $force_flag --step 2>&1 | tee -a "$MIGRATION_LOG"; then
        local post_migration_count=$(php artisan migrate:status 2>/dev/null | grep -c "Ran" || echo "0")
        local new_migrations=$((post_migration_count - pre_migration_count))
        
        if [ $new_migrations -gt 0 ]; then
            log "✅ Successfully ran $new_migrations new migrations"
        else
            log "✅ No new migrations to run"
        fi
        return 0
    else
        error "❌ Migration failed"
        return 1
    fi
}

# Function to run seeders
run_seeders() {
    local environment=${1:-"development"}
    local seeder_class=${2:-"DatabaseSeeder"}
    
    log "Running seeders: $seeder_class"
    
    local force_flag=""
    if [ "$environment" != "development" ]; then
        force_flag="--force"
    fi
    
    if [ "$seeder_class" = "DatabaseSeeder" ]; then
        if php artisan db:seed $force_flag 2>&1 | tee -a "$MIGRATION_LOG"; then
            log "✅ Seeders completed successfully"
            return 0
        else
            error "❌ Seeder failed"
            return 1
        fi
    else
        if php artisan db:seed --class="$seeder_class" $force_flag 2>&1 | tee -a "$MIGRATION_LOG"; then
            log "✅ Seeder $seeder_class completed successfully"
            return 0
        else
            error "❌ Seeder $seeder_class failed"
            return 1
        fi
    fi
}

# Function to fix known migration issues
fix_known_issues() {
    log "Fixing known migration issues..."
    
    # Fix translations table structure
    log "Checking translations table structure..."
    php artisan tinker --execute="
    if (Schema::hasTable('translations')) {
        \$columns = Schema::getColumnListing('translations');
        if (!in_array('translationable_type', \$columns) || !in_array('translationable_id', \$columns)) {
            echo 'Fixing translations table structure...';
            Schema::dropIfExists('translations');
            Schema::create('translations', function (\$table) {
                \$table->id();
                \$table->string('translationable_type');
                \$table->unsignedBigInteger('translationable_id');
                \$table->string('locale');
                \$table->string('key');
                \$table->text('value');
                \$table->timestamps();
                \$table->index(['translationable_type', 'translationable_id']);
            });
            echo 'Translations table structure fixed.';
        } else {
            echo 'Translations table structure is correct.';
        }
    } else {
        echo 'Translations table does not exist, will be created by migration.';
    }
    " 2>&1 | tee -a "$MIGRATION_LOG"
    
    # Add essential business settings
    log "Ensuring essential business settings exist..."
    php artisan tinker --execute="
    use App\Models\BusinessSetting;
    
    // Essential settings for the application to function
    \$essential_settings = [
        'system_language' => json_encode([
            ['id' => 1, 'name' => 'english', 'code' => 'en', 'status' => 1, 'default' => true, 'direction' => 'ltr']
        ]),
        'cash_on_delivery' => '1',
        'digital_payment' => '1',
        'business_name' => 'Tamam',
        'currency_code' => 'USD',
        'currency_symbol' => '$',
        'admin_login_url' => 'admin',
        'store_login_url' => 'store',
        'social_login' => json_encode([]),
        'apple_login' => json_encode([])
    ];
    
    foreach (\$essential_settings as \$key => \$value) {
        BusinessSetting::updateOrCreate(['key' => \$key], ['value' => \$value]);
        echo \"Updated setting: \$key\n\";
    }
    echo 'Essential business settings updated.';
    " 2>&1 | tee -a "$MIGRATION_LOG"
    
    # Ensure admin user exists
    log "Ensuring admin user exists..."
    php artisan tinker --execute="
    use App\Models\Admin;
    use Illuminate\Support\Facades\Hash;
    
    if (Admin::where('email', 'admin@admin.com')->doesntExist()) {
        \$admin = new Admin();
        \$admin->f_name = 'Admin';
        \$admin->l_name = 'User';
        \$admin->phone = '1234567890';
        \$admin->email = 'admin@admin.com';
        \$admin->password = Hash::make('12345678');
        \$admin->save();
        echo 'Admin user created: admin@admin.com / 12345678';
    } else {
        echo 'Admin user already exists: admin@admin.com';
    }
    " 2>&1 | tee -a "$MIGRATION_LOG"
    
    # Fix schedule_at column issue (recurring problem)
    log "Checking orders table for schedule_at column..."
    php artisan tinker --execute="
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\DB;
    
    if (Schema::hasTable('orders')) {
        if (!Schema::hasColumn('orders', 'schedule_at')) {
            echo 'Adding missing schedule_at column to orders table...';
            DB::statement('ALTER TABLE orders ADD COLUMN schedule_at TIMESTAMP NULL DEFAULT NULL AFTER created_at');
            echo 'schedule_at column added successfully.';
        } else {
            echo 'schedule_at column already exists.';
        }
        
        // Populate NULL values
        \$nullCount = DB::table('orders')->whereNull('schedule_at')->count();
        if (\$nullCount > 0) {
            echo \"Found {\$nullCount} orders with null schedule_at. Updating...\";
            \$updated = DB::table('orders')->whereNull('schedule_at')->update(['schedule_at' => DB::raw('created_at')]);
            echo \"Updated {\$updated} orders with schedule_at values.\";
        } else {
            echo 'All orders have schedule_at values.';
        }
    } else {
        echo 'Orders table does not exist yet.';
    }
    " 2>&1 | tee -a "$MIGRATION_LOG"
    
    log "✅ Known issues fixed"
}

# Function to test critical endpoints
test_endpoints() {
    log "Testing critical endpoints..."
    local server_url="http://localhost:8000"
    local test_results=0
    
    # Test API config endpoint
    if curl -s -f "$server_url/api/v1/config" >/dev/null 2>&1; then
        log "✅ API config endpoint working"
    else
        error "❌ API config endpoint failed"
        ((test_results++))
    fi
    
    # Test admin login page (check if it loads without fatal errors)
    local admin_response=$(curl -s -o /dev/null -w "%{http_code}" "$server_url/login/admin" 2>/dev/null)
    if [ "$admin_response" = "200" ] || [ "$admin_response" = "500" ]; then
        if curl -s "$server_url/login/admin" | grep -q "Login\|Admin" 2>/dev/null; then
            log "✅ Admin login page accessible"
        else
            warning "⚠️  Admin login page has errors but is reachable"
        fi
    else
        error "❌ Admin login page not accessible (HTTP $admin_response)"
        ((test_results++))
    fi
    
    if [ $test_results -eq 0 ]; then
        log "✅ All critical endpoints tested successfully"
        return 0
    else
        error "❌ $test_results endpoint tests failed"
        return 1
    fi
}

# Function to show migration status
show_status() {
    log "Migration Status Report"
    echo "========================"
    
    # Database connection
    if check_db_connection; then
        echo "Database: ✅ Connected"
    else
        echo "Database: ❌ Connection Failed"
        return 1
    fi
    
    # Migration status
    local total_migrations=$(ls database/migrations/*.php | wc -l)
    local ran_migrations=$(php artisan migrate:status 2>/dev/null | grep -c "Ran" || echo "0")
    local pending_migrations=$((total_migrations - ran_migrations))
    
    echo "Migrations: $ran_migrations/$total_migrations completed"
    if [ $pending_migrations -gt 0 ]; then
        echo "Pending: $pending_migrations migrations"
    fi
    
    # Table count
    local table_count=$(php artisan db:show 2>/dev/null | grep "Tables" | grep -o '[0-9]*' || echo "0")
    echo "Database Tables: $table_count"
    
    echo "========================"
}

# Function to rollback migrations
rollback_migrations() {
    local steps=${1:-1}
    log "Rolling back $steps migration step(s)..."
    
    if php artisan migrate:rollback --step="$steps" --force 2>&1 | tee -a "$MIGRATION_LOG"; then
        log "✅ Rollback completed successfully"
        return 0
    else
        error "❌ Rollback failed"
        return 1
    fi
}

# Main execution function
main() {
    local command=${1:-"status"}
    local environment=${2:-"development"}
    
    cd "$PROJECT_ROOT"
    
    log "=== Tamam Migration Manager Started ==="
    log "Command: $command"
    log "Environment: $environment"
    log "Project Root: $PROJECT_ROOT"
    log "Log File: $MIGRATION_LOG"
    
    case $command in
        "fresh")
            log "Starting fresh migration process..."
            if check_db_connection; then
                backup_database
                validate_migrations
                fix_known_issues
                run_migrations "$environment"
                run_seeders "$environment" "BusinessSettingSeeder"
                run_seeders "$environment" "AdminSeeder"
                test_endpoints
                show_status
            fi
            ;;
        "migrate")
            log "Running migrations only..."
            if check_db_connection; then
                backup_database
                validate_migrations
                run_migrations "$environment"
                show_status
            fi
            ;;
        "seed")
            log "Running seeders only..."
            local seeder_class=${3:-"DatabaseSeeder"}
            if check_db_connection; then
                run_seeders "$environment" "$seeder_class"
            fi
            ;;
        "fix")
            log "Fixing known issues..."
            if check_db_connection; then
                fix_known_issues
                test_endpoints
            fi
            ;;
        "test")
            log "Testing endpoints..."
            test_endpoints
            ;;
        "rollback")
            local steps=${3:-1}
            log "Rolling back migrations..."
            rollback_migrations "$steps"
            show_status
            ;;
        "status")
            show_status
            ;;
        "validate")
            validate_migrations
            ;;
        *)
            echo "Usage: $0 {fresh|migrate|seed|fix|test|rollback|status|validate} [environment] [options]"
            echo ""
            echo "Commands:"
            echo "  fresh     - Complete migration process (backup, migrate, seed, fix, test)"
            echo "  migrate   - Run migrations only"
            echo "  seed      - Run seeders [seeder_class]"
            echo "  fix       - Fix known migration issues"
            echo "  test      - Test critical endpoints"
            echo "  rollback  - Rollback migrations [steps]"
            echo "  status    - Show migration status"
            echo "  validate  - Validate migration files"
            echo ""
            echo "Environments: development, staging, production"
            exit 1
            ;;
    esac
    
    log "=== Tamam Migration Manager Completed ==="
}

# Run main function with all arguments
main "$@"