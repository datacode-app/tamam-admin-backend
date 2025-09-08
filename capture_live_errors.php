<?php
/**
 * LIVE ERROR CAPTURE SYSTEM
 * This will catch and fix the exact error as it happens
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class LiveErrorCapture {
    private $errorsCaught = [];
    
    public function __construct() {
        echo "📡 LIVE ERROR CAPTURE SYSTEM ACTIVE\n";
        echo "=" . str_repeat("=", 50) . "\n";
        echo "Monitoring for real-time database errors...\n\n";
    }
    
    public function startMonitoring() {
        // Override the DB connection to catch errors
        DB::listen(function ($query) {
            // Log all queries that might cause issues
            if (strpos($query->sql, 'schedule_at') !== false) {
                echo "🔍 QUERY DETECTED: " . $query->sql . "\n";
                echo "   Bindings: " . json_encode($query->bindings) . "\n";
                echo "   Time: " . $query->time . "ms\n\n";
            }
        });
        
        // Test all the dangerous operations
        $this->testDangerousOperations();
        
        // Monitor for specific error conditions
        $this->monitorErrorConditions();
    }
    
    private function testDangerousOperations() {
        echo "1. 🧪 TESTING OPERATIONS THAT COMMONLY FAIL...\n";
        
        $dangerousOps = [
            'Dashboard Load' => function() {
                // Simulate dashboard controller operations
                return $this->runWithErrorCapture(function() {
                    $searching_for_dm = DB::table('orders')
                        ->whereNull('delivery_man_id')
                        ->whereIn('order_type', ['delivery', 'parcel'])
                        ->whereNotIn('order_status', ['delivered', 'failed', 'canceled', 'refund_requested', 'refund_request_canceled', 'refunded'])
                        ->where(function($q) {
                            $q->where('order_type', 'take_away')->orWhere('order_type', 'delivery');
                        })
                        ->where(function($query) {
                            $query->whereRaw('created_at <> schedule_at')
                                  ->where(function($q) {
                                      $q->whereBetween('schedule_at', [now(), now()->addMinutes(30)])
                                        ->orWhere('schedule_at', '<', now());
                                  })
                                  ->orWhereRaw('created_at = schedule_at');
                        })
                        ->count();
                    
                    return $searching_for_dm;
                });
            },
            
            'Order Statistics' => function() {
                return $this->runWithErrorCapture(function() {
                    return DB::table('orders')
                        ->whereDate('schedule_at', now()->toDateString())
                        ->count();
                });
            },
            
            'Scheduled Orders' => function() {
                return $this->runWithErrorCapture(function() {
                    return DB::table('orders')
                        ->whereRaw('created_at <> schedule_at')
                        ->where('scheduled', 1)
                        ->count();
                });
            }
        ];
        
        foreach ($dangerousOps as $name => $operation) {
            echo "   Testing: $name...\n";
            
            try {
                $result = $operation();
                echo "   ✅ SUCCESS: $result\n";
            } catch (Exception $e) {
                echo "   ❌ ERROR CAUGHT: " . $e->getMessage() . "\n";
                $this->handleLiveError($e, $name);
            }
        }
        
        echo "✅ Dangerous operations testing complete\n\n";
    }
    
    private function runWithErrorCapture($callback) {
        try {
            return $callback();
        } catch (QueryException $e) {
            if (strpos($e->getMessage(), 'schedule_at') !== false) {
                echo "🎯 CAUGHT THE EXACT ERROR!\n";
                echo "Error: " . $e->getMessage() . "\n";
                echo "SQL: " . $e->getSql() . "\n";
                
                throw $e; // Re-throw for upper handler
            }
            throw $e;
        }
    }
    
    private function handleLiveError($error, $context) {
        echo "🚨 LIVE ERROR DETECTED!\n";
        echo "Context: $context\n";
        echo "Error: " . $error->getMessage() . "\n";
        
        $this->errorsCaught[] = [
            'context' => $context,
            'error' => $error->getMessage(),
            'time' => now()->toDateTimeString()
        ];
        
        // Try to auto-fix
        if (strpos($error->getMessage(), 'Unknown column') !== false) {
            echo "🔧 ATTEMPTING AUTO-FIX...\n";
            
            if (preg_match('/Unknown column \'(\w+)\'/', $error->getMessage(), $matches)) {
                $missingColumn = $matches[1];
                echo "Missing column detected: $missingColumn\n";
                
                $this->autoFixColumn($missingColumn);
            }
        }
    }
    
    private function autoFixColumn($column) {
        echo "🔧 Auto-fixing column: $column\n";
        
        $fixes = [
            'schedule_at' => "ALTER TABLE orders ADD COLUMN schedule_at TIMESTAMP NULL",
            'delivery_man_id' => "ALTER TABLE orders ADD COLUMN delivery_man_id BIGINT UNSIGNED NULL",
            'order_type' => "ALTER TABLE orders ADD COLUMN order_type VARCHAR(255) NOT NULL DEFAULT 'delivery'",
            'order_status' => "ALTER TABLE orders ADD COLUMN order_status VARCHAR(255) NOT NULL DEFAULT 'pending'",
            'scheduled' => "ALTER TABLE orders ADD COLUMN scheduled TINYINT(1) DEFAULT 0"
        ];
        
        if (isset($fixes[$column])) {
            try {
                DB::statement($fixes[$column]);
                echo "✅ Column $column added successfully\n";
                
                // Initialize values
                if ($column === 'schedule_at') {
                    DB::statement("UPDATE orders SET schedule_at = created_at WHERE schedule_at IS NULL");
                    echo "✅ Column $column initialized\n";
                }
                
            } catch (Exception $e) {
                echo "❌ Failed to add column: " . $e->getMessage() . "\n";
            }
        }
    }
    
    private function monitorErrorConditions() {
        echo "2. 🔍 MONITORING ERROR CONDITIONS...\n";
        
        // Check for common error conditions
        $conditions = [
            'Null schedule_at values' => DB::table('orders')->whereNull('schedule_at')->count(),
            'Missing delivery_man_id' => DB::table('orders')->whereNull('delivery_man_id')->count(),
            'Orders without order_type' => DB::table('orders')->where('order_type', '')->orWhereNull('order_type')->count(),
            'Orders without order_status' => DB::table('orders')->where('order_status', '')->orWhereNull('order_status')->count()
        ];
        
        foreach ($conditions as $condition => $count) {
            echo "   $condition: $count\n";
            
            if ($count > 0 && strpos($condition, 'schedule_at') !== false) {
                echo "   🔧 Fixing null schedule_at values...\n";
                DB::statement("UPDATE orders SET schedule_at = created_at WHERE schedule_at IS NULL");
                echo "   ✅ Fixed $count null schedule_at values\n";
            }
        }
        
        echo "✅ Error condition monitoring complete\n\n";
    }
    
    public function generateReport() {
        echo "3. 📋 LIVE ERROR CAPTURE REPORT\n";
        echo "=" . str_repeat("=", 50) . "\n";
        
        if (empty($this->errorsCaught)) {
            echo "🎉 NO LIVE ERRORS DETECTED!\n";
            echo "The database is working correctly at this moment.\n\n";
            
            echo "🤔 POSSIBLE REASONS FOR YOUR ERROR:\n";
            echo "1. Timing issue - error happens at specific times\n";
            echo "2. Multiple database environments\n"; 
            echo "3. Cached queries or connections\n";
            echo "4. Different user permissions\n";
            echo "5. Race conditions during high load\n\n";
            
            echo "💡 NEXT STEPS:\n";
            echo "1. Clear all Laravel caches: php artisan cache:clear\n";
            echo "2. Clear config cache: php artisan config:clear\n";
            echo "3. Clear view cache: php artisan view:clear\n";
            echo "4. Restart the server completely\n";
            echo "5. Run this monitor during the exact time you get the error\n";
            
        } else {
            echo "🚨 ERRORS CAPTURED: " . count($this->errorsCaught) . "\n";
            foreach ($this->errorsCaught as $error) {
                echo "   Context: " . $error['context'] . "\n";
                echo "   Error: " . $error['error'] . "\n";
                echo "   Time: " . $error['time'] . "\n";
                echo "   ---\n";
            }
        }
    }
}

// Run the live error capture
$monitor = new LiveErrorCapture();
$monitor->startMonitoring();
$monitor->generateReport();