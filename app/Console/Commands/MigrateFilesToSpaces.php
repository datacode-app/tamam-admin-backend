<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MigrateFilesToSpaces extends Command
{
    protected $signature = 'files:migrate-to-spaces {--dry-run : Show what would be migrated without actually doing it}';
    protected $description = 'Migrate existing files from local storage to DigitalOcean Spaces';

    public function handle()
    {
        $this->info('🚀 Starting file migration to DigitalOcean Spaces');
        
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No files will actually be moved');
        }
        
        // Test Spaces connection first
        if (!$this->testSpacesConnection()) {
            $this->error('❌ Cannot connect to DigitalOcean Spaces. Check your configuration.');
            return 1;
        }
        
        $this->info('✅ Spaces connection successful');
        
        // Get all files from local public disk
        $localFiles = Storage::disk('public')->allFiles();
        $this->info('📁 Found ' . count($localFiles) . ' files in local storage');
        
        if (empty($localFiles)) {
            $this->info('No files to migrate.');
            return 0;
        }
        
        $migratedCount = 0;
        $errors = [];
        
        foreach ($localFiles as $file) {
            try {
                if ($dryRun) {
                    $this->line('Would migrate: ' . $file);
                } else {
                    // Copy file to Spaces
                    $fileContent = Storage::disk('public')->get($file);
                    Storage::disk('spaces')->put($file, $fileContent);
                    
                    $this->line('✅ Migrated: ' . $file);
                    $migratedCount++;
                }
            } catch (\Exception $e) {
                $error = 'Failed to migrate ' . $file . ': ' . $e->getMessage();
                $this->error($error);
                $errors[] = $error;
            }
        }
        
        if (!$dryRun) {
            $this->info('\n📊 Migration Summary:');
            $this->info('Successfully migrated: ' . $migratedCount . ' files');
            $this->info('Errors: ' . count($errors));
            
            if (!empty($errors)) {
                $this->warn('\nErrors encountered:');
                foreach ($errors as $error) {
                    $this->line('  - ' . $error);
                }
            }
            
            // Update database URLs (placeholder - implement based on your schema)
            $this->updateDatabaseUrls();
        }
        
        return 0;
    }
    
    private function testSpacesConnection()
    {
        try {
            Storage::disk('spaces')->put('test-connection.txt', 'Connection test');
            Storage::disk('spaces')->delete('test-connection.txt');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    private function updateDatabaseUrls()
    {
        $this->info('\n🔄 Updating database URLs...');
        
        // Update common file URL fields
        // Adjust table and column names based on your schema
        
        $tables = [
            'stores' => ['logo', 'cover_photo'],
            'items' => ['image'],
            'banners' => ['image'],
            'users' => ['image'],
            'admins' => ['image'],
            'brands' => ['image'],
        ];
        
        $spacesUrl = env('DO_SPACES_URL');
        $appUrl = env('APP_URL');
        
        foreach ($tables as $table => $columns) {
            try {
                foreach ($columns as $column) {
                    $updated = DB::table($table)
                        ->where($column, 'like', $appUrl . '%')
                        ->update([
                            $column => DB::raw("REPLACE($column, '$appUrl/storage', '$spacesUrl')")
                        ]);
                    
                    if ($updated > 0) {
                        $this->line("Updated $updated URLs in $table.$column");
                    }
                }
            } catch (\Exception $e) {
                $this->warn("Could not update URLs in $table: " . $e->getMessage());
            }
        }
        
        $this->info('✅ Database URL updates completed');
    }
}
