<?php
/**
 * DATABASE CONNECTION DIAGNOSTIC TOOL
 * Shows exactly which databases we're connecting to and from where
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

echo "🔍 DATABASE CONNECTION DIAGNOSTIC\n";
echo "=" . str_repeat("=", 60) . "\n\n";

try {
    // 1. Show all configured database connections
    echo "1. 📋 CONFIGURED DATABASE CONNECTIONS:\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $connections = Config::get('database.connections');
    foreach ($connections as $name => $config) {
        echo "Connection: '$name'\n";
        echo "  Driver: " . ($config['driver'] ?? 'N/A') . "\n";
        echo "  Host: " . ($config['host'] ?? 'N/A') . "\n";
        echo "  Port: " . ($config['port'] ?? 'N/A') . "\n";
        echo "  Database: " . ($config['database'] ?? 'N/A') . "\n";
        echo "  Username: " . ($config['username'] ?? 'N/A') . "\n";
        echo "\n";
    }
    
    // 2. Show default connection
    echo "2. 🎯 DEFAULT CONNECTION:\n";
    echo "-" . str_repeat("-", 40) . "\n";
    $defaultConnection = Config::get('database.default');
    echo "Default: $defaultConnection\n\n";
    
    // 3. Test actual connection details
    echo "3. 🔌 ACTIVE CONNECTION TEST:\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    // Get connection info from actual database
    $connectionInfo = DB::select('SELECT CONNECTION_ID(), DATABASE(), USER(), @@hostname, @@port');
    
    foreach ($connectionInfo as $info) {
        echo "Connection ID: " . $info->{'CONNECTION_ID()'} . "\n";
        echo "Current Database: " . $info->{'DATABASE()'} . "\n";
        echo "User: " . $info->{'USER()'} . "\n";
        echo "Server: " . $info->{'@@hostname'} . "\n";
        echo "Port: " . $info->{'@@port'} . "\n";
    }
    
    echo "\n4. 📊 ORDERS TABLE STATUS:\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    // Check if orders table exists and its structure
    try {
        $tables = DB::select("SHOW TABLES LIKE 'orders'");
        if (empty($tables)) {
            echo "❌ Orders table does NOT exist in current database!\n";
        } else {
            echo "✅ Orders table exists\n";
            
            // Check columns
            $columns = DB::select("DESCRIBE orders");
            $columnNames = array_column($columns, 'Field');
            
            echo "Total columns: " . count($columnNames) . "\n";
            echo "Schedule_at column: " . (in_array('schedule_at', $columnNames) ? "✅ EXISTS" : "❌ MISSING") . "\n";
            
            if (in_array('schedule_at', $columnNames)) {
                $scheduleInfo = array_filter($columns, fn($col) => $col->Field === 'schedule_at');
                $scheduleInfo = array_values($scheduleInfo)[0] ?? null;
                if ($scheduleInfo) {
                    echo "Schedule_at type: " . $scheduleInfo->Type . "\n";
                    echo "Schedule_at null: " . $scheduleInfo->Null . "\n";
                    echo "Schedule_at default: " . $scheduleInfo->Default . "\n";
                }
            }
            
            // Count orders
            $orderCount = DB::table('orders')->count();
            echo "Total orders: $orderCount\n";
        }
    } catch (Exception $e) {
        echo "❌ Error checking orders table: " . $e->getMessage() . "\n";
    }
    
    echo "\n5. 🌐 ENVIRONMENT INFO:\n";
    echo "-" . str_repeat("-", 40) . "\n";
    echo "PHP Version: " . PHP_VERSION . "\n";
    echo "Laravel Version: " . app()->version() . "\n";
    echo "Environment: " . app()->environment() . "\n";
    echo "Config Cache: " . (app()->configurationIsCached() ? "CACHED" : "NOT CACHED") . "\n";
    
    // Check .env file
    echo "\n6. 📄 .ENV FILE STATUS:\n";
    echo "-" . str_repeat("-", 40) . "\n";
    echo "DB_CONNECTION: " . env('DB_CONNECTION', 'NOT SET') . "\n";
    echo "DB_HOST: " . env('DB_HOST', 'NOT SET') . "\n";
    echo "DB_PORT: " . env('DB_PORT', 'NOT SET') . "\n";
    echo "DB_DATABASE: " . env('DB_DATABASE', 'NOT SET') . "\n";
    echo "DB_USERNAME: " . env('DB_USERNAME', 'NOT SET') . "\n";
    
    echo "\n7. 🧪 RUNNING PROBLEM QUERY:\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    try {
        $count = DB::table('orders')
            ->whereNull('delivery_man_id')
            ->whereIn('order_type', ['delivery', 'parcel'])
            ->whereNotIn('order_status', ['delivered', 'failed', 'canceled', 'refund_requested', 'refund_request_canceled', 'refunded'])
            ->where(function($query) {
                $query->where('order_type', 'take_away')
                      ->orWhere('order_type', 'delivery');
            })
            ->count();
        echo "✅ Query successful! Found $count orders\n";
    } catch (Exception $e) {
        echo "❌ Query failed: " . $e->getMessage() . "\n";
        echo "This confirms the schedule_at column issue!\n";
    }
    
    echo "\n🎯 DIAGNOSIS COMPLETE!\n";
    
} catch (Exception $e) {
    echo "💥 CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}