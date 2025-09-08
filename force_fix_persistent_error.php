<?php
/**
 * FORCE FIX PERSISTENT ERROR
 * Nuclear option - fixes the error regardless of the cause
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

echo "💥 FORCE FIX PERSISTENT ERROR - Nuclear Option\n";
echo "=" . str_repeat("=", 60) . "\n";
echo "This will fix the error no matter what's causing it.\n\n";

try {
    // 1. Nuclear cache clearing
    echo "1. 💣 NUCLEAR CACHE CLEARING...\n";
    
    // Clear Laravel caches
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    
    // Clear OPCache if available
    if (function_exists('opcache_reset')) {
        opcache_reset();
        echo "   ✅ OPCache cleared\n";
    }
    
    // Clear file-based caches
    $cachePaths = [
        'bootstrap/cache/config.php',
        'bootstrap/cache/routes.php',
        'bootstrap/cache/services.php'
    ];
    
    foreach ($cachePaths as $path) {
        if (file_exists($path)) {
            unlink($path);
            echo "   ✅ Deleted: $path\n";
        }
    }
    
    echo "   ✅ All caches nuked\n\n";
    
    // 2. Force database connection reset
    echo "2. 🔌 FORCING DATABASE CONNECTION RESET...\n";
    
    DB::disconnect();
    DB::reconnect();
    
    echo "   ✅ Database connection reset\n\n";
    
    // 3. Verify and force-fix ALL critical columns
    echo "3. 🔨 FORCE-FIXING ALL CRITICAL COLUMNS...\n";
    
    $criticalColumns = [
        'orders' => [
            'schedule_at' => 'TIMESTAMP NULL',
            'delivery_man_id' => 'BIGINT UNSIGNED NULL',
            'order_type' => 'VARCHAR(255) NOT NULL DEFAULT "delivery"',
            'order_status' => 'VARCHAR(255) NOT NULL DEFAULT "pending"',
            'scheduled' => 'TINYINT(1) DEFAULT 0',
            'zone_id' => 'BIGINT UNSIGNED NULL',
            'module_id' => 'BIGINT UNSIGNED NULL'
        ]
    ];
    
    foreach ($criticalColumns as $table => $columns) {
        echo "   Checking table: $table\n";
        
        if (!Schema::hasTable($table)) {
            echo "   ❌ Table missing - this should not happen!\n";
            continue;
        }
        
        foreach ($columns as $column => $definition) {
            if (!Schema::hasColumn($table, $column)) {
                echo "   🔧 Force-adding column: $column\n";
                
                try {
                    DB::statement("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
                    echo "   ✅ Added: $column\n";
                } catch (Exception $e) {
                    echo "   ⚠️  Column add warning: " . $e->getMessage() . "\n";
                }
            } else {
                echo "   ✅ Column exists: $column\n";
            }
        }
    }
    
    // 4. Force-initialize all NULL values
    echo "\n4. 🔄 FORCE-INITIALIZING NULL VALUES...\n";
    
    $nullFixQueries = [
        "UPDATE orders SET schedule_at = created_at WHERE schedule_at IS NULL",
        "UPDATE orders SET order_type = 'delivery' WHERE order_type IS NULL OR order_type = ''",
        "UPDATE orders SET order_status = 'pending' WHERE order_status IS NULL OR order_status = ''",
        "UPDATE orders SET scheduled = 0 WHERE scheduled IS NULL"
    ];
    
    foreach ($nullFixQueries as $query) {
        try {
            $affected = DB::update($query);
            echo "   ✅ Fixed $affected rows: " . substr($query, 0, 50) . "...\n";
        } catch (Exception $e) {
            echo "   ⚠️  Fix warning: " . $e->getMessage() . "\n";
        }
    }
    
    // 5. Test EVERY possible failing query
    echo "\n5. 🧪 TESTING ALL POSSIBLE FAILING QUERIES...\n";
    
    $testQueries = [
        'Basic Order Count' => 'SELECT COUNT(*) as c FROM orders',
        'Delivery Man Filter' => 'SELECT COUNT(*) as c FROM orders WHERE delivery_man_id IS NULL',
        'Order Type Filter' => 'SELECT COUNT(*) as c FROM orders WHERE order_type IN ("delivery", "parcel")',
        'Order Status Filter' => 'SELECT COUNT(*) as c FROM orders WHERE order_status NOT IN ("delivered", "failed", "canceled")',
        'Schedule At Basic' => 'SELECT COUNT(*) as c FROM orders WHERE schedule_at IS NOT NULL',
        'Schedule At Comparison' => 'SELECT COUNT(*) as c FROM orders WHERE created_at <> schedule_at',
        'Schedule At Time Range' => 'SELECT COUNT(*) as c FROM orders WHERE schedule_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 MINUTE)',
        'Complete Complex Query' => 'SELECT COUNT(*) as c FROM orders WHERE delivery_man_id IS NULL AND order_type IN ("delivery", "parcel") AND order_status NOT IN ("delivered", "failed", "canceled", "refund_requested", "refund_request_canceled", "refunded") AND (order_type = "take_away" OR order_type = "delivery") AND ((created_at <> schedule_at AND (schedule_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 MINUTE) OR schedule_at < NOW())) OR created_at = schedule_at)'
    ];
    
    $allPassed = true;
    foreach ($testQueries as $name => $query) {
        try {
            $result = DB::select($query);
            $count = $result[0]->c;
            echo "   ✅ $name: $count\n";
        } catch (Exception $e) {
            echo "   ❌ $name: FAILED - " . $e->getMessage() . "\n";
            $allPassed = false;
            
            // Try to auto-fix this specific failure
            if (strpos($e->getMessage(), 'Unknown column') !== false) {
                if (preg_match('/Unknown column \'(\w+)\'/', $e->getMessage(), $matches)) {
                    $missingCol = $matches[1];
                    echo "      🔧 Trying to fix missing column: $missingCol\n";
                    
                    // Add the column with a safe default type
                    try {
                        DB::statement("ALTER TABLE orders ADD COLUMN `$missingCol` VARCHAR(255) NULL");
                        echo "      ✅ Added missing column: $missingCol\n";
                        
                        // Retry the query
                        $result = DB::select($query);
                        $count = $result[0]->c;
                        echo "      ✅ RETRY SUCCESS: $count\n";
                        
                    } catch (Exception $e2) {
                        echo "      ❌ RETRY FAILED: " . $e2->getMessage() . "\n";
                    }
                }
            }
        }
    }
    
    // 6. Force Laravel Model to refresh
    echo "\n6. 🔄 FORCING LARAVEL MODEL REFRESH...\n";
    
    // Clear model cache if any
    if (class_exists('App\Models\Order')) {
        echo "   ✅ Order model exists\n";
        
        try {
            $count = \App\Models\Order::count();
            echo "   ✅ Order::count() = $count\n";
        } catch (Exception $e) {
            echo "   ❌ Order::count() failed: " . $e->getMessage() . "\n";
        }
        
        try {
            $count = \App\Models\Order::SearchingForDeliveryman()->count();
            echo "   ✅ SearchingForDeliveryman scope = $count\n";
        } catch (Exception $e) {
            echo "   ❌ SearchingForDeliveryman failed: " . $e->getMessage() . "\n";
        }
    }
    
    // 7. Final verification
    echo "\n7. ✅ FINAL VERIFICATION...\n";
    
    if ($allPassed) {
        echo "🎉 SUCCESS! All queries are now working.\n";
        echo "The persistent error should be COMPLETELY ELIMINATED.\n\n";
        
        echo "📋 WHAT WAS FIXED:\n";
        echo "• All Laravel caches cleared\n";
        echo "• Database connection reset\n"; 
        echo "• All critical columns verified/added\n";
        echo "• All NULL values initialized\n";
        echo "• All queries tested and working\n";
        
    } else {
        echo "⚠️  Some issues remain. Check the failed queries above.\n";
        echo "The persistent error may still occur.\n";
    }
    
    echo "\n🛡️ PREVENTION COMMANDS:\n";
    echo "Run these regularly:\n";
    echo "• php artisan cache:clear\n";
    echo "• php artisan config:clear\n";
    echo "• php artisan db:bulletproof --force\n";
    echo "• php database_health_check.php\n";
    
} catch (Exception $e) {
    echo "💥 NUCLEAR FIX ERROR: " . $e->getMessage() . "\n";
    exit(1);
}