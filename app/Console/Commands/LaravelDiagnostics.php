<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class LaravelDiagnostics extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'diagnose:laravel 
                          {--migration : Check migration files for common errors}
                          {--count : Check for unsafe count() usage}
                          {--database : Test database connectivity}
                          {--views : Check for view-related issues}
                          {--all : Run all diagnostic checks}';

    /**
     * The console command description.
     */
    protected $description = 'Systematic Laravel diagnostics and error prevention';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Laravel Systematic Diagnostics');
        $this->info('=================================');

        if ($this->option('all') || $this->option('database')) {
            $this->checkDatabase();
        }

        if ($this->option('all') || $this->option('migration')) {
            $this->checkMigrations();
        }

        if ($this->option('all') || $this->option('count')) {
            $this->checkUnsafeCountUsage();
        }

        if ($this->option('all') || $this->option('views')) {
            $this->checkViews();
        }

        $this->newLine();
        $this->info('✅ Diagnostics completed!');
    }

    private function checkDatabase()
    {
        $this->info('📊 Database Connectivity Check');
        
        try {
            DB::connection()->getPdo();
            $this->info('✅ Database connection successful');
            
            // Check critical tables
            $tables = ['users', 'items', 'stores', 'orders'];
            foreach ($tables as $table) {
                try {
                    $count = DB::table($table)->count();
                    $this->info("✅ Table '$table': $count records");
                } catch (\Exception $e) {
                    $this->error("❌ Table '$table': " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $this->error('❌ Database connection failed: ' . $e->getMessage());
        }
    }

    private function checkMigrations()
    {
        $this->info('🔧 Migration Files Check');
        
        $migrationPath = database_path('migrations');
        $files = glob($migrationPath . '/*.php');
        
        $issues = 0;
        foreach ($files as $file) {
            $content = file_get_contents($file);
            
            // Check for common issues
            if (strpos($content, 'count(') !== false && strpos($content, '??') === false) {
                $this->warn("⚠️  " . basename($file) . ": Contains potentially unsafe count() usage");
                $issues++;
            }
            
            if (strpos($content, '$table->') !== false && strpos($content, 'Schema::dropIfExists') === false) {
                // Check for proper rollback in down() method
                if (strpos($content, 'public function down()') !== false) {
                    $downContent = substr($content, strpos($content, 'public function down()'));
                    if (strpos($downContent, 'Schema::dropIfExists') === false && 
                        strpos($downContent, 'Schema::drop') === false) {
                        $this->warn("⚠️  " . basename($file) . ": May be missing proper rollback in down() method");
                        $issues++;
                    }
                }
            }
        }
        
        if ($issues === 0) {
            $this->info("✅ No migration issues found in " . count($files) . " files");
        } else {
            $this->warn("⚠️  Found $issues potential issues in migrations");
        }
    }

    private function checkUnsafeCountUsage()
    {
        $this->info('🔢 Unsafe count() Usage Check');
        
        $paths = [
            app_path(),
            resource_path('views')
        ];
        
        $issues = 0;
        foreach ($paths as $path) {
            $files = glob($path . '/**/*.php', GLOB_BRACE);
            
            foreach ($files as $file) {
                if (strpos($file, 'vendor/') !== false) continue;
                
                $content = file_get_contents($file);
                $lines = explode("\n", $content);
                
                foreach ($lines as $lineNum => $line) {
                    if (preg_match('/count\(\$[^)]+\)/', $line) && 
                        strpos($line, '??') === false && 
                        strpos($line, 'safe_count') === false) {
                        
                        $this->warn("⚠️  " . basename($file) . ":".($lineNum+1)." - Potentially unsafe count(): " . trim($line));
                        $issues++;
                    }
                }
            }
        }
        
        if ($issues === 0) {
            $this->info("✅ No unsafe count() usage found");
        } else {
            $this->warn("⚠️  Found $issues potentially unsafe count() usages");
            $this->info("💡 Consider using SafeHelper::safe_count() or \$var ?? [] patterns");
        }
    }

    private function checkViews()
    {
        $this->info('👁️  View Files Check');
        
        $viewPath = resource_path('views');
        $files = glob($viewPath . '/**/*.blade.php', GLOB_BRACE);
        
        $issues = 0;
        foreach ($files as $file) {
            $content = file_get_contents($file);
            
            // Check for undefined variable usage patterns
            if (preg_match_all('/\$([a-zA-Z_][a-zA-Z0-9_]*)(?!\s*\?\?)/i', $content, $matches)) {
                $variables = array_unique($matches[1]);
                
                // Common variables that might be undefined
                $risky = ['top_customers', 'top_restaurants', 'top_sell', 'data'];
                $foundRisky = array_intersect($variables, $risky);
                
                if (!empty($foundRisky)) {
                    $this->warn("⚠️  " . basename($file) . ": Uses variables that might be undefined: " . implode(', ', $foundRisky));
                    $issues++;
                }
            }
        }
        
        if ($issues === 0) {
            $this->info("✅ No obvious view variable issues found in " . count($files) . " files");
        } else {
            $this->warn("⚠️  Found $issues potential view variable issues");
            $this->info("💡 Consider using \$variable ?? [] or \$variable ?? 'default' patterns");
        }
    }
}