<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateToDatabaseCluster extends Command
{
    protected $signature = 'db:migrate-to-cluster {--test : Test connection without migrating}';
    protected $description = 'Migrate existing database to DigitalOcean Database Cluster';

    public function handle()
    {
        $this->info('🗄️  Migrating to DigitalOcean Database Cluster');
        $this->info('============================================');
        
        $testOnly = $this->option('test');
        
        // Test cluster connection
        if (!$this->testClusterConnection()) {
            $this->error('❌ Cannot connect to database cluster');
            return 1;
        }
        
        $this->info('✅ Cluster connection successful');
        
        if ($testOnly) {
            $this->info('Test completed successfully. Use without --test to migrate.');
            return 0;
        }
        
        // Backup current database
        if (!$this->createBackup()) {
            $this->error('❌ Failed to create database backup');
            return 1;
        }
        
        // Migrate data to cluster
        if (!$this->migrateToCluster()) {
            $this->error('❌ Migration to cluster failed');
            return 1;
        }
        
        // Verify migration
        if (!$this->verifyMigration()) {
            $this->error('❌ Migration verification failed');
            return 1;
        }
        
        $this->info('✅ Successfully migrated to Database Cluster');
        return 0;
    }
    
    private function testClusterConnection()
    {
        try {
            DB::connection('mysql_cluster')->select('SELECT 1 as test');
            return true;
        } catch (\Exception $e) {
            $this->error('Connection error: ' . $e->getMessage());
            return false;
        }
    }
    
    private function createBackup()
    {
        $this->info('\n📦 Creating database backup...');
        
        $backupFile = storage_path('app/backups/cluster_migration_' . date('Y-m-d_H-i-s') . '.sql');
        $backupDir = dirname($backupFile);
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        try {
            $host = config('database.connections.mysql.host');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            
            $command = sprintf(
                'mysqldump -h%s -u%s -p%s %s > %s 2>/dev/null',
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($backupFile)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($backupFile)) {
                $this->info('✅ Backup created: ' . basename($backupFile));
                return true;
            } else {
                $this->error('❌ Backup creation failed');
                return false;
            }
        } catch (\Exception $e) {
            $this->error('Backup error: ' . $e->getMessage());
            return false;
        }
    }
    
    private function migrateToCluster()
    {
        $this->info('\n🔄 Migrating data to cluster...');
        
        try {
            // Get list of tables from current database
            $tables = DB::select('SHOW TABLES');
            $tableCount = count($tables);
            
            $this->info("Found $tableCount tables to migrate");
            
            $bar = $this->output->createProgressBar($tableCount);
            $bar->start();
            
            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];
                
                // Skip if table already exists in cluster
                if ($this->tableExistsInCluster($tableName)) {
                    $bar->advance();
                    continue;
                }
                
                // Export table structure and data
                $this->migrateTable($tableName);
                $bar->advance();
            }
            
            $bar->finish();
            $this->line('');
            
            return true;
        } catch (\Exception $e) {
            $this->error('Migration error: ' . $e->getMessage());
            return false;
        }
    }
    
    private function tableExistsInCluster($tableName)
    {
        try {
            return Schema::connection('mysql_cluster')->hasTable($tableName);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    private function migrateTable($tableName)
    {
        // This is a simplified version - you might want to use mysqldump for complex tables
        try {
            // Get table structure
            $createStatement = DB::select("SHOW CREATE TABLE `$tableName`")[0];
            $createSql = $createStatement->{"Create Table"};
            
            // Create table in cluster
            DB::connection('mysql_cluster')->statement($createSql);
            
            // Copy data if table has records
            $recordCount = DB::table($tableName)->count();
            if ($recordCount > 0) {
                // For large tables, you might want to chunk this
                $data = DB::table($tableName)->get()->toArray();
                
                foreach (array_chunk($data, 1000) as $chunk) {
                    $insertData = array_map(function($row) {
                        return (array)$row;
                    }, $chunk);
                    
                    DB::connection('mysql_cluster')->table($tableName)->insert($insertData);
                }
            }
        } catch (\Exception $e) {
            $this->warn("Failed to migrate table $tableName: " . $e->getMessage());
        }
    }
    
    private function verifyMigration()
    {
        $this->info('\n🔍 Verifying migration...');
        
        try {
            // Compare table counts
            $originalTables = collect(DB::select('SHOW TABLES'));
            $clusterTables = collect(DB::connection('mysql_cluster')->select('SHOW TABLES'));
            
            if ($originalTables->count() !== $clusterTables->count()) {
                $this->error('Table count mismatch');
                return false;
            }
            
            // Verify key tables have data
            $keyTables = ['users', 'stores', 'items', 'orders'];
            
            foreach ($keyTables as $table) {
                if (Schema::hasTable($table)) {
                    $originalCount = DB::table($table)->count();
                    $clusterCount = DB::connection('mysql_cluster')->table($table)->count();
                    
                    if ($originalCount !== $clusterCount) {
                        $this->error("Record count mismatch in $table: $originalCount vs $clusterCount");
                        return false;
                    }
                    
                    $this->line("✅ $table: $clusterCount records");
                }
            }
            
            return true;
        } catch (\Exception $e) {
            $this->error('Verification error: ' . $e->getMessage());
            return false;
        }
    }
}
