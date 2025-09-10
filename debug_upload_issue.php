<?php
/**
 * Debug upload success but no image display issue
 * Run: php debug_upload_issue.php
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Store;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

echo "=== DEBUGGING UPLOAD SUCCESS BUT NO IMAGE DISPLAY ===\n";

// Test 1: Check current storage configuration
echo "\n1. Storage Configuration Check:\n";
echo "   Local Storage Setting: " . (Helpers::get_business_settings('local_storage') ?? 'NULL') . "\n";
echo "   3rd Party Storage Setting: " . (Helpers::get_business_settings('3rd_party_storage') ?? 'NULL') . "\n";
echo "   Active Disk (Helpers::getDisk()): " . Helpers::getDisk() . "\n";

// Test 2: Check S3 configuration details
echo "\n2. S3/DigitalOcean Spaces Configuration:\n";
$s3Config = config('filesystems.disks.s3');
echo "   Endpoint: " . ($s3Config['endpoint'] ?? 'NOT SET') . "\n";
echo "   Bucket: " . ($s3Config['bucket'] ?? 'NOT SET') . "\n";
echo "   URL: " . ($s3Config['url'] ?? 'NOT SET') . "\n";
echo "   Key: " . (isset($s3Config['key']) ? 'SET' : 'NOT SET') . "\n";
echo "   Secret: " . (isset($s3Config['secret']) ? 'SET' : 'NOT SET') . "\n";

// Test 3: Test actual S3 connection and upload capability
echo "\n3. S3 Connection and Upload Test:\n";
try {
    $testContent = 'test-content-' . time();
    $testPath = 'test-uploads/test-file-' . time() . '.txt';
    
    Storage::disk('s3')->put($testPath, $testContent);
    echo "   ✅ S3 Upload Test: SUCCESS\n";
    
    $exists = Storage::disk('s3')->exists($testPath);
    echo "   ✅ S3 File Exists Check: " . ($exists ? 'SUCCESS' : 'FAILED') . "\n";
    
    $url = Storage::disk('s3')->url($testPath);
    echo "   ✅ S3 URL Generation: $url\n";
    
    // Clean up test file
    Storage::disk('s3')->delete($testPath);
    echo "   ✅ S3 Cleanup: SUCCESS\n";
    
} catch (Exception $e) {
    echo "   ❌ S3 Test FAILED: " . $e->getMessage() . "\n";
}

// Test 4: Check a real store record
echo "\n4. Real Store Upload Investigation:\n";
$store = Store::first();
if ($store) {
    echo "   Store ID: {$store->id}\n";
    echo "   Store Name: {$store->name}\n";
    echo "   Logo Filename: " . ($store->logo ?? 'NULL') . "\n";
    echo "   Cover Photo Filename: " . ($store->cover_photo ?? 'NULL') . "\n";
    
    // Check storage metadata
    $logoStorage = $store->storage()->where('key', 'logo')->first();
    $coverStorage = $store->storage()->where('key', 'cover_photo')->first();
    
    echo "   Logo Storage Metadata: " . ($logoStorage ? $logoStorage->value : 'NULL') . "\n";
    echo "   Cover Storage Metadata: " . ($coverStorage ? $coverStorage->value : 'NULL') . "\n";
    
    // Test URL generation
    echo "   Logo Full URL: " . $store->logo_full_url . "\n";
    echo "   Cover Full URL: " . $store->cover_photo_full_url . "\n";
    
    // Check if actual files exist on S3
    if ($store->logo) {
        $logoPath = 'store/' . $store->logo;
        $logoExists = Storage::disk('s3')->exists($logoPath);
        echo "   Logo File Exists on S3: " . ($logoExists ? 'YES' : 'NO') . "\n";
        if ($logoExists) {
            echo "   Logo S3 URL: " . Storage::disk('s3')->url($logoPath) . "\n";
        }
    }
    
    if ($store->cover_photo) {
        $coverPath = 'store/cover/' . $store->cover_photo;
        $coverExists = Storage::disk('s3')->exists($coverPath);
        echo "   Cover File Exists on S3: " . ($coverExists ? 'YES' : 'NO') . "\n";
        if ($coverExists) {
            echo "   Cover S3 URL: " . Storage::disk('s3')->url($coverPath) . "\n";
        }
    }
}

// Test 5: Check URL accessibility
echo "\n5. URL Accessibility Test:\n";
if (isset($store) && $store->logo) {
    $logoPath = 'store/' . $store->logo;
    try {
        $s3Url = Storage::disk('s3')->url($logoPath);
        echo "   Generated S3 URL: $s3Url\n";
        
        // Try to access the URL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $s3Url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "   URL HTTP Status: $httpCode " . ($httpCode === 200 ? '✅' : '❌') . "\n";
        
    } catch (Exception $e) {
        echo "   URL Test Error: " . $e->getMessage() . "\n";
    }
}

// Test 6: Check recent uploads in database
echo "\n6. Recent Upload Activity Check:\n";
$recentStores = Store::where('updated_at', '>=', now()->subHours(24))->get();
echo "   Stores updated in last 24 hours: " . $recentStores->count() . "\n";

foreach ($recentStores->take(3) as $recentStore) {
    echo "   - Store ID {$recentStore->id}: Logo={$recentStore->logo}, Updated=" . $recentStore->updated_at . "\n";
}

// Test 7: Check if the issue is in the get_full_url function
echo "\n7. URL Generation Logic Test:\n";
if (isset($store) && $store->logo) {
    echo "   Testing Helpers::get_full_url function:\n";
    
    // Test with correct storage metadata
    $urlWithS3 = Helpers::get_full_url('store', $store->logo, 's3');
    echo "   URL with 's3' type: $urlWithS3\n";
    
    // Test with wrong storage metadata  
    $urlWithPublic = Helpers::get_full_url('store', $store->logo, 'public');
    echo "   URL with 'public' type: $urlWithPublic\n";
    
    // Check if there's a difference
    if ($urlWithS3 !== $urlWithPublic) {
        echo "   ✅ Storage type affects URL generation correctly\n";
    } else {
        echo "   ❌ Storage type doesn't affect URL generation - this could be the issue!\n";
    }
}

echo "\n🔍 DIAGNOSIS:\n";

// Check for common issues
$issues = [];
$solutions = [];

if (Helpers::getDisk() !== 's3') {
    $issues[] = "Storage disk is not set to S3";
    $solutions[] = "Run the storage fix script again";
}

if (!isset($s3Config['endpoint']) || !isset($s3Config['bucket'])) {
    $issues[] = "S3 configuration is incomplete";
    $solutions[] = "Check .env file for AWS_* variables";
}

if (isset($store) && $store->logo && isset($logoStorage) && $logoStorage->value !== 's3') {
    $issues[] = "Storage metadata is not set to S3";
    $solutions[] = "Re-run the metadata fix";
}

if (isset($store) && $store->logo && !$logoExists) {
    $issues[] = "Files don't actually exist on S3";
    $solutions[] = "Check if uploads are actually reaching S3";
}

if (empty($issues)) {
    echo "   ✅ No obvious configuration issues found\n";
    echo "   🔍 The issue might be:\n";
    echo "   1. Browser caching old placeholder images\n";
    echo "   2. Files uploading but with wrong names\n";
    echo "   3. S3 bucket permissions preventing public access\n";
    echo "   4. CDN URL not serving files properly\n";
} else {
    echo "   ❌ Issues Found:\n";
    foreach ($issues as $i => $issue) {
        echo "   " . ($i + 1) . ". $issue\n";
        echo "      Solution: {$solutions[$i]}\n";
    }
}

echo "\n=== DEBUG COMPLETE ===\n";