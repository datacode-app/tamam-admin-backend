<?php
/**
 * EMERGENCY SCHEDULE_AT COLUMN FIX
 * This fixes the recurring schedule_at column missing issue
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

echo "🚨 EMERGENCY SCHEDULE_AT COLUMN FIX\n";
echo "=" . str_repeat("=", 50) . "\n\n";

try {
    echo "🔍 Checking orders table structure...\n";
    
    // Get actual columns in orders table
    $columns = Schema::getColumnListing('orders');
    echo "Orders table has " . count($columns) . " columns\n";
    
    if (!in_array('schedule_at', $columns)) {
        echo "❌ schedule_at column is MISSING - adding it now...\n";
        
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('schedule_at')->nullable()->after('created_at');
            $table->index('schedule_at'); // Add index for better performance
        });
        
        echo "✅ schedule_at column added with index!\n";
    } else {
        echo "✅ schedule_at column exists\n";
        
        // Check if it has an index
        $indexes = DB::select("SHOW INDEX FROM orders WHERE Column_name = 'schedule_at'");
        if (empty($indexes)) {
            echo "⚠️  Adding missing index for schedule_at...\n";
            Schema::table('orders', function (Blueprint $table) {
                $table->index('schedule_at');
            });
            echo "✅ Index added!\n";
        } else {
            echo "✅ schedule_at column has proper index\n";
        }
    }
    
    echo "\n🧪 TESTING THE EXACT FAILING QUERY...\n";
    echo "Query: select count(*) from orders where delivery_man_id is null...\n";
    
    // Test the exact query that was failing
    $sql = "SELECT COUNT(*) as aggregate 
            FROM `orders` 
            WHERE `delivery_man_id` is null 
            AND `order_type` in ('delivery', 'parcel') 
            AND `order_status` not in ('delivered', 'failed', 'canceled', 'refund_requested', 'refund_request_canceled', 'refunded') 
            AND (`order_type` = 'take_away' OR `order_type` = 'delivery') 
            AND ((created_at <> schedule_at AND (`schedule_at` between ? and ? OR `schedule_at` < ?)) OR created_at = schedule_at)";
    
    $now = now();
    $future = now()->addMinutes(30);
    
    $result = DB::select($sql, [$now, $future, $now]);
    
    echo "✅ QUERY SUCCESSFUL! Count: " . $result[0]->aggregate . "\n";
    
    echo "\n🎉 EMERGENCY FIX COMPLETE!\n";
    echo "✅ schedule_at column exists and is indexed\n";
    echo "✅ All dashboard queries should work now\n";
    echo "✅ No more 'Column not found' errors\n";
    
} catch (Exception $e) {
    echo "💥 ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    // Try a more aggressive fix
    echo "\n🔧 ATTEMPTING AGGRESSIVE FIX...\n";
    try {
        DB::statement("ALTER TABLE orders ADD COLUMN schedule_at TIMESTAMP NULL AFTER created_at");
        DB::statement("CREATE INDEX idx_orders_schedule_at ON orders(schedule_at)");
        echo "✅ Aggressive fix applied!\n";
    } catch (Exception $e2) {
        echo "❌ Aggressive fix also failed: " . $e2->getMessage() . "\n";
    }
}