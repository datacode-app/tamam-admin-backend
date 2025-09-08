<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseHealthCheck extends Command
{
    protected $signature = 'db:health-check {--detailed : Show detailed health information}';
    protected $description = 'Check database cluster health and performance';

    public function handle()
    {
        $this->info('🏥 Database Cluster Health Check');
        $this->info('==============================');
        
        $detailed = $this->option('detailed');
        
        // Check primary connection
        $this->checkConnection('mysql_cluster', 'Primary Cluster');
        
        // Check read replica if configured
        if (config('database.connections.mysql_read')) {
            $this->checkConnection('mysql_read', 'Read Replica');
        }
        
        if ($detailed) {
            $this->showDetailedHealth();
        }
        
        return 0;
    }
    
    private function checkConnection($connection, $name)
    {
        $this->info("\n📡 Testing $name Connection");
        $this->info(str_repeat('-', 30));
        
        try {
            $start = microtime(true);
            $result = DB::connection($connection)->select('SELECT 1 as test, NOW() as server_time');
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            $this->info("✅ $name: Connected");
            $this->info("🕐 Response time: {$duration}ms");
            $this->info("⏰ Server time: " . $result[0]->server_time);
            
            // Test write capability
            try {
                DB::connection($connection)->table('health_checks')->insert([
                    'check_time' => now(),
                    'connection' => $connection
                ]);
                $this->info('✅ Write test: Passed');
            } catch (\Exception $e) {
                $this->warn('⚠️  Write test: ' . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            $this->error("❌ $name: Connection failed");
            $this->error('Error: ' . $e->getMessage());
        }
    }
    
    private function showDetailedHealth()
    {
        $this->info('\n📊 Detailed Health Information');
        $this->info('=============================');
        
        try {
            // Show process list
            $processes = DB::connection('mysql_cluster')->select('SHOW PROCESSLIST');
            $this->info('Active connections: ' . count($processes));
            
            // Show database size
            $size = DB::connection('mysql_cluster')->select('
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ');
            
            if (!empty($size)) {
                $this->info('Database size: ' . $size[0]->size_mb . ' MB');
            }
            
            // Show table status
            $tables = DB::connection('mysql_cluster')->select('
                SELECT 
                    table_name, 
                    table_rows, 
                    ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                ORDER BY (data_length + index_length) DESC 
                LIMIT 10
            ');
            
            $this->info('\nTop 10 largest tables:');
            $this->table(['Table', 'Rows', 'Size (MB)'], 
                collect($tables)->map(function($table) {
                    return [$table->table_name, $table->table_rows, $table->size_mb];
                })->toArray()
            );
            
        } catch (\Exception $e) {
            $this->error('Failed to get detailed health info: ' . $e->getMessage());
        }
    }
}
