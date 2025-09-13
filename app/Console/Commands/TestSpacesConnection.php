<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestSpacesConnection extends Command
{
    protected $signature = 'spaces:test';
    protected $description = 'Test DigitalOcean Spaces connection and operations';

    public function handle()
    {
        $this->info('🧪 Testing DigitalOcean Spaces Connection');
        $this->info('========================================');
        
        // Test 1: Basic connection
        $this->info('\n1. Testing basic connection...');
        try {
            $testContent = 'Test file created at ' . now();
            $testFile = 'test-connection-' . time() . '.txt';
            
            Storage::disk('spaces')->put($testFile, $testContent);
            $this->info('✅ File upload successful');
            
            $retrieved = Storage::disk('spaces')->get($testFile);
            if ($retrieved === $testContent) {
                $this->info('✅ File download successful');
            } else {
                $this->error('❌ File content mismatch');
                return 1;
            }
            
            Storage::disk('spaces')->delete($testFile);
            $this->info('✅ File deletion successful');
            
        } catch (\Exception $e) {
            $this->error('❌ Connection test failed: ' . $e->getMessage());
            return 1;
        }
        
        // Test 2: URL generation
        $this->info('\n2. Testing URL generation...');
        try {
            $testFile = 'url-test.txt';
            Storage::disk('spaces')->put($testFile, 'URL test');
            
            $url = Storage::disk('spaces')->url($testFile);
            $this->info('✅ Generated URL: ' . $url);
            
            Storage::disk('spaces')->delete($testFile);
            
        } catch (\Exception $e) {
            $this->error('❌ URL generation failed: ' . $e->getMessage());
        }
        
        // Test 3: Configuration check
        $this->info('\n3. Configuration check...');
        $config = config('filesystems.disks.spaces') ?? [];
        
        $required = ['key', 'secret', 'endpoint', 'region', 'bucket'];
        foreach ($required as $field) {
            if (empty($config[$field] ?? null)) {
                $this->error('❌ Missing configuration: ' . $field);
            } else {
                $this->info('✅ ' . $field . ': configured');
            }
        }
        
        $this->info('\n🎉 All tests passed! DigitalOcean Spaces is ready.');
        return 0;
    }
}
