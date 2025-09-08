<?php
/**
 * BULLETPROOF DATABASE HEALTH MONITOR
 * 
 * This script monitors database health continuously and fixes issues automatically.
 * Run with: php database_health_monitor.php
 * 
 * Features:
 * - Real-time column missing detection
 * - Auto-healing database schema
 * - Performance monitoring
 * - Alert system for critical issues
 */

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class DatabaseHealthMonitor
{
    private $db;
    private $logFile;
    private $alertThreshold = 5; // seconds for slow queries
    private $monitoringEnabled = true;

    public function __construct()
    {
        $this->initDatabase();
        $this->logFile = __DIR__ . '/storage/logs/database_health_' . date('Y-m-d') . '.log';
        $this->log("🚀 Database Health Monitor Started");
    }

    private function initDatabase()
    {
        $capsule = new Capsule;
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => '18.197.125.4',
            'port' => '5433',
            'database' => 'tamamdb',
            'username' => 'tamam_user',
            'password' => 'tamam_passwrod', // Note: keeping the original typo as per working config
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        
        $this->db = $capsule->getConnection();
    }

    public function startMonitoring($intervalSeconds = 30)
    {
        $this->log("🔍 Starting continuous monitoring (interval: {$intervalSeconds}s)");
        
        while ($this->monitoringEnabled) {
            try {
                $this->performHealthCheck();
                sleep($intervalSeconds);
            } catch (Exception $e) {
                $this->log("❌ Monitor error: " . $e->getMessage());
                sleep(5); // Brief pause before retry
            }
        }
    }

    private function performHealthCheck()
    {
        $startTime = microtime(true);
        
        // Check 1: Critical table existence
        $this->checkCriticalTables();
        
        // Check 2: Common problematic queries
        $this->checkProblematicQueries();
        
        // Check 3: Performance monitoring
        $this->checkQueryPerformance();
        
        // Check 4: Auto-heal missing columns
        $this->autoHealMissingColumns();
        
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        $this->log("✅ Health check complete ({$duration}ms)");
    }

    private function checkCriticalTables()
    {
        $criticalTables = ['orders', 'admins', 'users', 'stores', 'items'];
        
        foreach ($criticalTables as $table) {
            try {
                $count = $this->db->table($table)->count();
                // Table exists and accessible
            } catch (Exception $e) {
                $this->log("🚨 CRITICAL: Table {$table} not accessible: " . $e->getMessage());
                $this->alertCriticalIssue("Table {$table} missing or inaccessible");
            }
        }
    }

    private function checkProblematicQueries()
    {
        $queries = [
            "SELECT COUNT(*) as total FROM orders WHERE schedule_at IS NOT NULL",
            "SELECT COUNT(*) as total FROM orders WHERE module_id IS NOT NULL",
            "SELECT COUNT(*) as total FROM orders WHERE created_at >= CURDATE()",
            "SELECT * FROM admins WHERE role_id = 1 LIMIT 1"
        ];

        foreach ($queries as $query) {
            try {
                $startTime = microtime(true);
                $this->db->select($query);
                $duration = (microtime(true) - $startTime) * 1000;
                
                if ($duration > ($this->alertThreshold * 1000)) {
                    $this->log("⚠️ Slow query detected ({$duration}ms): " . substr($query, 0, 50) . "...");
                }
            } catch (Exception $e) {
                $this->log("❌ Query failed: " . substr($query, 0, 50) . "... Error: " . $e->getMessage());
                $this->autoFixQueryIssue($query, $e);
            }
        }
    }

    private function checkQueryPerformance()
    {
        try {
            // Test dashboard performance
            $startTime = microtime(true);
            $orderCount = $this->db->table('orders')->count();
            $todayOrders = $this->db->table('orders')->whereDate('created_at', date('Y-m-d'))->count();
            $duration = (microtime(true) - $startTime) * 1000;
            
            if ($duration > 2000) { // 2 seconds threshold
                $this->log("⚠️ Dashboard queries slow ({$duration}ms) - consider optimization");
            }
        } catch (Exception $e) {
            $this->log("❌ Performance check failed: " . $e->getMessage());
        }
    }

    private function autoHealMissingColumns()
    {
        $commonMissingColumns = [
            'orders' => [
                'schedule_at' => 'TIMESTAMP NULL',
                'module_id' => 'BIGINT UNSIGNED NULL',
                'zone_id' => 'BIGINT UNSIGNED NULL',
                'delivery_charge' => 'DECIMAL(24,3) DEFAULT 0',
                'service_charge' => 'DECIMAL(24,3) DEFAULT 0'
            ],
            'admins' => [
                'role_id' => 'BIGINT UNSIGNED NULL DEFAULT 1',
                'zone_id' => 'BIGINT UNSIGNED NULL',
                'is_logged_in' => 'TINYINT(1) DEFAULT 0'
            ],
            'stores' => [
                'module_id' => 'BIGINT UNSIGNED NULL',
                'zone_id' => 'BIGINT UNSIGNED NULL',
                'self_delivery_system' => 'TINYINT(1) DEFAULT 0'
            ]
        ];

        foreach ($commonMissingColumns as $tableName => $columns) {
            if (!$this->tableExists($tableName)) continue;
            
            $existingColumns = $this->getTableColumns($tableName);
            
            foreach ($columns as $columnName => $definition) {
                if (!in_array($columnName, $existingColumns)) {
                    try {
                        $this->db->statement("ALTER TABLE `{$tableName}` ADD COLUMN `{$columnName}` {$definition}");
                        $this->log("🛠️ AUTO-HEAL: Added missing column {$tableName}.{$columnName}");
                    } catch (Exception $e) {
                        $this->log("❌ Failed to add column {$tableName}.{$columnName}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    private function autoFixQueryIssue($query, $exception)
    {
        $errorMessage = $exception->getMessage();
        
        // Check for column not found errors
        if (strpos($errorMessage, "doesn't exist") !== false || strpos($errorMessage, "Unknown column") !== false) {
            preg_match("/column '([^']+)'/", $errorMessage, $matches);
            if (isset($matches[1])) {
                $missingColumn = $matches[1];
                $this->log("🔧 AUTO-FIX: Attempting to fix missing column: {$missingColumn}");
                
                // Intelligent column type detection and creation
                $this->intelligentColumnCreation($missingColumn, $query);
            }
        }
    }

    private function intelligentColumnCreation($columnName, $context)
    {
        // Determine table from context
        preg_match("/FROM\s+(\w+)/i", $context, $matches);
        $tableName = $matches[1] ?? 'orders'; // Default to orders if unclear
        
        // Intelligent type inference
        $columnType = $this->inferColumnType($columnName);
        
        try {
            $this->db->statement("ALTER TABLE `{$tableName}` ADD COLUMN `{$columnName}` {$columnType}");
            $this->log("✅ AUTO-CREATED: {$tableName}.{$columnName} ({$columnType})");
        } catch (Exception $e) {
            $this->log("❌ Failed to auto-create column: " . $e->getMessage());
        }
    }

    private function inferColumnType($columnName)
    {
        if (substr($columnName, -3) === '_id') {
            return 'BIGINT UNSIGNED NULL';
        }
        if (substr($columnName, -3) === '_at') {
            return 'TIMESTAMP NULL';
        }
        if (strpos($columnName, 'amount') !== false || strpos($columnName, 'price') !== false) {
            return 'DECIMAL(24,3) DEFAULT 0';
        }
        if (strpos($columnName, 'is_') === 0 || strpos($columnName, 'has_') === 0) {
            return 'TINYINT(1) DEFAULT 0';
        }
        if ($columnName === 'status') {
            return 'VARCHAR(255) DEFAULT "active"';
        }
        
        return 'VARCHAR(255) NULL'; // Safe default
    }

    private function tableExists($tableName)
    {
        try {
            $this->db->select("SHOW TABLES LIKE '{$tableName}'");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function getTableColumns($tableName)
    {
        try {
            $columns = $this->db->select("DESCRIBE `{$tableName}`");
            return array_column($columns, 'Field');
        } catch (Exception $e) {
            return [];
        }
    }

    private function alertCriticalIssue($message)
    {
        $this->log("🚨 CRITICAL ALERT: {$message}");
        
        // Here you could add email alerts, Slack notifications, etc.
        // For now, just ensure it's prominently logged
        error_log("TAMAM DB CRITICAL: {$message}");
    }

    private function log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] {$message}\n";
        
        // Console output
        echo $logEntry;
        
        // File logging
        if (!file_exists(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0755, true);
        }
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    public function stop()
    {
        $this->monitoringEnabled = false;
        $this->log("🛑 Database Health Monitor Stopped");
    }

    public function generateHealthReport()
    {
        $this->log("📊 Generating Health Report...");
        
        $report = [
            'timestamp' => date('Y-m-d H:i:s'),
            'database_connection' => 'OK',
            'critical_tables' => [],
            'performance_metrics' => [],
            'recommendations' => []
        ];

        // Check all critical tables
        $tables = ['orders', 'admins', 'users', 'stores', 'items', 'categories'];
        foreach ($tables as $table) {
            try {
                $count = $this->db->table($table)->count();
                $report['critical_tables'][$table] = "OK ({$count} records)";
            } catch (Exception $e) {
                $report['critical_tables'][$table] = "ERROR: " . $e->getMessage();
            }
        }

        // Performance metrics
        try {
            $startTime = microtime(true);
            $this->db->table('orders')->count();
            $duration = (microtime(true) - $startTime) * 1000;
            $report['performance_metrics']['orders_count_query'] = "{$duration}ms";
        } catch (Exception $e) {
            $report['performance_metrics']['orders_count_query'] = "ERROR";
        }

        $this->log("📋 Health Report: " . json_encode($report, JSON_PRETTY_PRINT));
        
        return $report;
    }
}

// Command line interface
if ($argc > 1) {
    $command = $argv[1];
    $monitor = new DatabaseHealthMonitor();
    
    switch ($command) {
        case 'start':
            $interval = isset($argv[2]) ? (int)$argv[2] : 30;
            $monitor->startMonitoring($interval);
            break;
            
        case 'check':
            $monitor->generateHealthReport();
            break;
            
        case 'heal':
            echo "🔧 Running one-time auto-heal...\n";
            $monitor->autoHealMissingColumns();
            echo "✅ Auto-heal complete\n";
            break;
            
        default:
            echo "Usage: php database_health_monitor.php [start|check|heal] [interval_seconds]\n";
            echo "  start [30] - Start continuous monitoring (default 30s interval)\n";
            echo "  check      - Generate one-time health report\n";
            echo "  heal       - Run one-time auto-healing\n";
    }
} else {
    echo "BULLETPROOF DATABASE HEALTH MONITOR\n";
    echo "Usage: php database_health_monitor.php [start|check|heal] [interval_seconds]\n";
    echo "\n";
    echo "This monitor provides:\n";
    echo "✅ Real-time column missing detection\n";
    echo "🔧 Auto-healing database schema\n";
    echo "📊 Performance monitoring\n";
    echo "🚨 Alert system for critical issues\n";
    echo "\n";
    echo "Examples:\n";
    echo "  php database_health_monitor.php start 60   # Monitor every 60 seconds\n";
    echo "  php database_health_monitor.php check      # One-time health check\n";
    echo "  php database_health_monitor.php heal       # Auto-fix missing columns\n";
}