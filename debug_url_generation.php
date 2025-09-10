<?php
/**
 * Debug URL generation issue - why placeholders instead of real images
 * Run: php debug_url_generation.php
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Store;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Storage;

echo "=== DEBUGGING URL GENERATION ISSUE ===\n";

// Get a store with recent uploads
$store = Store::where('updated_at', '>=', now()->subHours(24))->first();
if (!$store) {
    $store = Store::first();
}

echo "\n1. Store Information:\n";
echo "   Store ID: {$store->id}\n";
echo "   Store Name: {$store->name}\n";
echo "   Logo Filename: " . ($store->logo ?? 'NULL') . "\n";
echo "   Updated At: {$store->updated_at}\n";

// Check storage metadata
echo "\n2. Storage Metadata Check:\n";
$storageRecords = $store->storage;
echo "   Total storage records: " . $storageRecords->count() . "\n";
foreach ($storageRecords as $record) {
    echo "   - {$record->key}: {$record->value}\n";
}

// Test the getLogoFullUrlAttribute method step by step
echo "\n3. Step-by-step URL Generation Debug:\n";

if ($store->logo) {
    echo "   Logo filename: {$store->logo}\n";
    
    // Check if storage relationship works
    $logoStorage = $store->storage()->where('key', 'logo')->first();
    echo "   Logo storage record found: " . ($logoStorage ? 'YES' : 'NO') . "\n";
    if ($logoStorage) {
        echo "   Logo storage value: {$logoStorage->value}\n";
    }
    
    // Check what the model method returns
    echo "   store->logo_full_url: {$store->logo_full_url}\n";
    
    // Check if file exists on S3
    $logoPath = 'store/' . $store->logo;
    $existsOnS3 = Storage::disk('s3')->exists($logoPath);
    echo "   File exists on S3: " . ($existsOnS3 ? 'YES' : 'NO') . "\n";
    
    if ($existsOnS3) {
        $s3Url = Storage::disk('s3')->url($logoPath);
        echo "   Actual S3 URL: $s3Url\n";
        
        // Test HTTP accessibility
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $s3Url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "   S3 URL HTTP Status: $httpCode\n";
    }
    
    // Test Helpers::get_full_url directly
    echo "\n4. Testing Helpers::get_full_url directly:\n";
    $testUrl1 = Helpers::get_full_url('store', $store->logo, 's3');
    echo "   get_full_url('store', '{$store->logo}', 's3'): $testUrl1\n";
    
    $testUrl2 = Helpers::get_full_url('store', $store->logo, 'public');
    echo "   get_full_url('store', '{$store->logo}', 'public'): $testUrl2\n";
    
    // Check if the function even looks at the type parameter
    if ($testUrl1 === $testUrl2) {
        echo "   ❌ ISSUE: Storage type parameter is being ignored!\n";
    } else {
        echo "   ✅ Storage type parameter affects URL generation\n";
    }
}

// Let's manually trace through the get_full_url function
echo "\n5. Manual get_full_url Function Trace:\n";

if ($store->logo) {
    $path = 'store';
    $data = $store->logo;
    $type = $logoStorage ? $logoStorage->value : 'unknown';
    
    echo "   Parameters: path='$path', data='$data', type='$type'\n";
    
    // Check the conditions in get_full_url
    echo "   Checking conditions:\n";
    
    // Condition 1: S3 check
    if ($data && $type == 's3' && Storage::disk('s3')->exists($path .'/'. $data)) {
        echo "   ✅ S3 condition met - should return S3 URL\n";
        $expectedUrl = Storage::disk('s3')->url($path .'/'. $data);
        echo "   Expected S3 URL: $expectedUrl\n";
    } else {
        echo "   ❌ S3 condition not met:\n";
        echo "     - data exists: " . ($data ? 'YES' : 'NO') . "\n";
        echo "     - type is s3: " . ($type == 's3' ? 'YES' : 'NO') . "\n";
        echo "     - file exists on S3: " . (Storage::disk('s3')->exists($path .'/'. $data) ? 'YES' : 'NO') . "\n";
    }
    
    // Condition 2: Public check
    if ($data && Storage::disk('public')->exists($path .'/'. $data)) {
        echo "   ✅ Public condition met - would return public URL\n";
        $publicUrl = asset('storage') . '/' . $path . '/' . $data;
        echo "   Would return public URL: $publicUrl\n";
    } else {
        echo "   ❌ Public condition not met\n";
    }
    
    // Condition 3: Placeholder
    $placeholderKey = 'store';
    echo "   Would use placeholder for key: '$placeholderKey'\n";
    
    // Let's check what placeholder would be returned
    $placeholders = [
        'store' => asset('assets/admin/img/160x160/img1.jpg'),
    ];
    echo "   Placeholder URL: " . ($placeholders[$placeholderKey] ?? 'default') . "\n";
}

// Check if there's a caching issue
echo "\n6. Checking for Caching Issues:\n";
$cachedUrl = $store->logo_full_url;
$store->refresh();
$refreshedUrl = $store->logo_full_url;

echo "   Cached URL: $cachedUrl\n";
echo "   Refreshed URL: $refreshedUrl\n";
echo "   URLs match: " . ($cachedUrl === $refreshedUrl ? 'YES' : 'NO') . "\n";

echo "\n🔍 FINAL DIAGNOSIS:\n";

if ($store->logo && $existsOnS3 && $logoStorage && $logoStorage->value === 's3') {
    if (strpos($store->logo_full_url, 'digitaloceanspaces.com') !== false) {
        echo "   ✅ Everything should be working correctly\n";
    } else {
        echo "   ❌ Files exist on S3, metadata is correct, but URL generation is broken\n";
        echo "   🔧 The issue is in the get_full_url function logic\n";
    }
} else {
    echo "   ❌ Missing components:\n";
    if (!$existsOnS3) echo "   - File doesn't exist on S3\n";
    if (!$logoStorage) echo "   - Storage metadata missing\n";
    if ($logoStorage && $logoStorage->value !== 's3') echo "   - Storage metadata incorrect\n";
}

echo "\n=== DEBUG COMPLETE ===\n";