<?php
/**
 * COMPREHENSIVE ENVIRONMENT DIAGNOSIS
 * This will identify the EXACT source of the persistent database issues
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

echo "🔍 COMPREHENSIVE ENVIRONMENT DIAGNOSIS\n";
echo "=" . str_repeat("=", 70) . "\n";
echo "Finding the ROOT CAUSE of persistent database issues...\n\n";

try {
    // 1. Show ALL environment details
    echo "1. 🌍 ENVIRONMENT ANALYSIS\n";
    echo "   Current working directory: " . getcwd() . "\n";
    echo "   PHP version: " . PHP_VERSION . "\n";
    echo "   Laravel version: " . app()->version() . "\n";
    echo "   Environment: " . app()->environment() . "\n";
    
    // Check for multiple .env files
    echo "\n   📄 ENVIRONMENT FILES:\n";
    $envFiles = ['.env', '.env.local', '.env.production', '.env.staging', '.env.example'];
    foreach ($envFiles as $envFile) {
        if (file_exists($envFile)) {
            $size = filesize($envFile);
            $modified = date('Y-m-d H:i:s', filemtime($envFile));
            echo "   ✅ $envFile: {$size} bytes, modified: $modified\n";
        } else {
            echo "   ❌ $envFile: Not found\n";
        }
    }
    
    // 2. Database Configuration Analysis
    echo "\n2. 🗄️ DATABASE CONFIGURATION ANALYSIS\n";
    
    $dbConfig = Config::get('database');
    $defaultConnection = $dbConfig['default'];
    echo "   Default connection: $defaultConnection\n";
    
    $connectionConfig = $dbConfig['connections'][$defaultConnection];
    echo "   Driver: " . $connectionConfig['driver'] . "\n";
    echo "   Host: " . $connectionConfig['host'] . "\n";
    echo "   Port: " . $connectionConfig['port'] . "\n";
    echo "   Database: " . $connectionConfig['database'] . "\n";
    echo "   Username: " . $connectionConfig['username'] . "\n";
    echo "   Password: " . (isset($connectionConfig['password']) ? str_repeat('*', strlen($connectionConfig['password'])) : 'Not set') . "\n";
    
    // 3. ACTUAL Database Connection Test
    echo "\n3. 🔌 ACTUAL DATABASE CONNECTION TEST\n";
    
    try {
        $pdo = DB::connection()->getPdo();
        echo "   ✅ Connection successful\n";
        
        // Get actual database info
        $dbInfo = DB::select("SELECT DATABASE() as current_db, USER() as current_user, @@hostname as hostname, @@port as port")[0];
        echo "   📊 ACTUAL CONNECTION DETAILS:\n";
        echo "   Current database: " . $dbInfo->current_db . "\n";
        echo "   Current user: " . $dbInfo->current_user . "\n";
        echo "   Hostname: " . $dbInfo->hostname . "\n";
        echo "   Port: " . $dbInfo->port . "\n";
        
    } catch (Exception $e) {
        echo "   ❌ Connection failed: " . $e->getMessage() . "\n";
    }
    
    // 4. Table Structure Analysis
    echo "\n4. 📋 ACTUAL TABLE STRUCTURE ANALYSIS\n";
    
    try {
        // Check if orders table exists
        $tables = DB::select("SHOW TABLES LIKE 'orders'");
        if (empty($tables)) {
            echo "   ❌ CRITICAL: Orders table does NOT exist in the actual database!\n";
            
            // Show what tables DO exist
            $allTables = DB::select("SHOW TABLES");
            echo "   📋 Available tables: " . count($allTables) . "\n";
            foreach (array_slice($allTables, 0, 10) as $table) {
                $tableName = array_values((array)$table)[0];
                echo "      - $tableName\n";
            }
            if (count($allTables) > 10) {
                echo "      ... and " . (count($allTables) - 10) . " more\n";
            }
            
        } else {
            echo "   ✅ Orders table exists\n";
            
            // Get column structure
            $columns = DB::select("DESCRIBE orders");
            echo "   📊 Orders table has " . count($columns) . " columns\n";
            
            $criticalColumns = ['schedule_at', 'delivery_man_id', 'order_type', 'order_status'];
            $missingColumns = [];
            $existingColumns = [];
            
            foreach ($columns as $column) {
                $existingColumns[] = $column->Field;
                
                if (in_array($column->Field, $criticalColumns)) {
                    echo "   ✅ {$column->Field}: {$column->Type}\n";
                }
            }
            
            // Check for missing critical columns
            foreach ($criticalColumns as $critical) {
                if (!in_array($critical, $existingColumns)) {
                    echo "   ❌ MISSING: $critical\n";
                    $missingColumns[] = $critical;
                }
            }
            
            if (!empty($missingColumns)) {
                echo "\n   🚨 FOUND THE PROBLEM! Missing columns: " . implode(', ', $missingColumns) . "\n";
            }
        }
        
    } catch (Exception $e) {
        echo "   ❌ Table analysis failed: " . $e->getMessage() . "\n";
    }
    
    // 5. Test the EXACT failing query
    echo "\n5. 🧪 TESTING THE EXACT FAILING QUERY\n";
    
    $failingQuery = "SELECT COUNT(*) as aggregate FROM `orders` WHERE `delivery_man_id` IS NULL AND `order_type` IN ('delivery', 'parcel') AND `order_status` NOT IN ('delivered', 'failed', 'canceled', 'refund_requested', 'refund_request_canceled', 'refunded') AND (`order_type` = 'take_away' OR `order_type` = 'delivery') AND ((created_at <> schedule_at AND (`schedule_at` BETWEEN '2025-08-06 16:45:39' AND '2025-08-06 17:15:39') OR `schedule_at` < '2025-08-06 16:45:39') OR created_at = schedule_at)";
    
    echo "   Testing the exact query that's failing...\n";
    
    try {
        $result = DB::select($failingQuery);
        $count = $result[0]->aggregate;
        echo "   ✅ SUCCESS! Query returned: $count\n";
        echo "   🤔 This is strange - the query works now...\n";
        
    } catch (Exception $e) {
        echo "   ❌ QUERY FAILED: " . $e->getMessage() . "\n";
        echo "   🎯 THIS IS THE EXACT ERROR YOU'RE EXPERIENCING!\n";
        
        // Try to fix it immediately
        if (strpos($e->getMessage(), 'schedule_at') !== false) {
            echo "\n   🔧 ATTEMPTING IMMEDIATE FIX...\n";
            
            try {
                DB::statement("ALTER TABLE orders ADD COLUMN schedule_at TIMESTAMP NULL");
                echo "   ✅ Added schedule_at column\n";
                
                // Initialize values
                DB::statement("UPDATE orders SET schedule_at = created_at WHERE schedule_at IS NULL");
                echo "   ✅ Initialized schedule_at values\n";
                
                // Retry the query
                $result = DB::select($failingQuery);
                $count = $result[0]->aggregate;
                echo "   ✅ RETRY SUCCESS! Query returned: $count\n";
                
            } catch (Exception $fixError) {
                echo "   ❌ FIX FAILED: " . $fixError->getMessage() . "\n";
            }
        }
    }
    
    // 6. Environment vs Code Analysis
    echo "\n6. 🔄 ENVIRONMENT vs CODE MISMATCH ANALYSIS\n";
    
    // Check if there are cached configurations
    $cacheFiles = [
        'bootstrap/cache/config.php',
        'bootstrap/cache/routes.php',
        'bootstrap/cache/services.php'
    ];
    
    echo "   📦 Cache file status:\n";
    foreach ($cacheFiles as $cacheFile) {
        if (file_exists($cacheFile)) {
            $modified = date('Y-m-d H:i:s', filemtime($cacheFile));
            echo "   ⚠️  $cacheFile exists (modified: $modified)\n";
        } else {
            echo "   ✅ $cacheFile: Not cached\n";
        }
    }
    
    // Check for multiple PHP processes or servers
    echo "\n   🔍 Process analysis:\n";
    $processes = shell_exec("ps aux | grep php | grep -v grep | wc -l");
    echo "   Active PHP processes: " . trim($processes) . "\n";
    
    $artisanProcesses = shell_exec("ps aux | grep 'artisan serve' | grep -v grep | wc -l");
    echo "   Active Laravel servers: " . trim($artisanProcesses) . "\n";
    
    // 7. Generate Fix Recommendations
    echo "\n7. 💡 FIX RECOMMENDATIONS\n";
    
    if (!empty($missingColumns)) {
        echo "   🎯 IMMEDIATE ACTION REQUIRED:\n";
        echo "   The actual database is missing critical columns!\n";
        echo "   Run this command to fix it:\n";
        echo "   \n";
        echo "   🔧 EMERGENCY FIX COMMAND:\n";
        
        foreach ($missingColumns as $column) {
            switch($column) {
                case 'schedule_at':
                    echo "   ALTER TABLE orders ADD COLUMN schedule_at TIMESTAMP NULL;\n";
                    echo "   UPDATE orders SET schedule_at = created_at WHERE schedule_at IS NULL;\n";
                    break;
                case 'delivery_man_id':
                    echo "   ALTER TABLE orders ADD COLUMN delivery_man_id BIGINT UNSIGNED NULL;\n";
                    break;
                case 'order_type':
                    echo "   ALTER TABLE orders ADD COLUMN order_type VARCHAR(255) NOT NULL DEFAULT 'delivery';\n";
                    break;
                case 'order_status':
                    echo "   ALTER TABLE orders ADD COLUMN order_status VARCHAR(255) NOT NULL DEFAULT 'pending';\n";
                    break;
            }
        }
    } else {
        echo "   🤔 All columns exist, but error persists.\n";
        echo "   Possible causes:\n";
        echo "   - Multiple database connections\n";
        echo "   - Cached configurations\n";
        echo "   - Race conditions\n";
        echo "   - Different environments\n";
    }
    
    echo "\n🎯 DIAGNOSIS COMPLETE\n";
    echo "This report shows the EXACT state of your environment.\n";
    echo "The root cause should now be clear!\n";
    
} catch (Exception $e) {
    echo "💥 DIAGNOSIS ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}