<?php
/**
 * Test the actual upload process to identify where it fails
 * Run: php test_upload_process.php
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

echo "=== TESTING UPLOAD PROCESS ===\n";

// Test 1: Check if the upload method works at all
echo "\n1. Testing Helpers::upload() method directly:\n";

// Create a test file to simulate upload
$testContent = 'test image content ' . time();
$tempFile = tempnam(sys_get_temp_dir(), 'test_upload');
file_put_contents($tempFile, $testContent);

// Create a mock uploaded file
try {
    $uploadedFile = new UploadedFile(
        $tempFile,
        'test-image.png',
        'image/png',
        null,
        true
    );

    echo "   Created test file: " . $uploadedFile->getClientOriginalName() . "\n";
    echo "   File size: " . $uploadedFile->getSize() . " bytes\n";
    echo "   MIME type: " . $uploadedFile->getMimeType() . "\n";

    // Test Helpers::upload directly
    echo "\n   Testing Helpers::upload('store/', 'png', \$uploadedFile)...\n";
    
    $result = Helpers::upload('store/', 'png', $uploadedFile);
    echo "   Upload result: " . ($result ?: 'NULL/FALSE') . "\n";
    
    if ($result && $result !== 'def.png') {
        // Check if file actually exists on S3
        $filePath = 'store/' . $result;
        $exists = Storage::disk('s3')->exists($filePath);
        echo "   File exists on S3: " . ($exists ? 'YES' : 'NO') . "\n";
        
        if ($exists) {
            echo "   S3 URL: " . Storage::disk('s3')->url($filePath) . "\n";
            // Clean up
            Storage::disk('s3')->delete($filePath);
            echo "   Cleaned up test file\n";
        }
    }

} catch (Exception $e) {
    echo "   ❌ Upload test failed: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 2: Test the update method
echo "\n2. Testing Helpers::update() method:\n";

try {
    $uploadedFile2 = new UploadedFile(
        $tempFile,
        'test-update-image.png',
        'image/png',
        null,
        true
    );
    
    echo "   Testing Helpers::update('store/', 'old-file.png', 'png', \$uploadedFile)...\n";
    
    $updateResult = Helpers::update('store/', 'old-file.png', 'png', $uploadedFile2);
    echo "   Update result: " . ($updateResult ?: 'NULL/FALSE') . "\n";
    
    if ($updateResult && $updateResult !== 'def.png') {
        $filePath = 'store/' . $updateResult;
        $exists = Storage::disk('s3')->exists($filePath);
        echo "   Updated file exists on S3: " . ($exists ? 'YES' : 'NO') . "\n";
        
        if ($exists) {
            Storage::disk('s3')->delete($filePath);
            echo "   Cleaned up updated file\n";
        }
    }

} catch (Exception $e) {
    echo "   ❌ Update test failed: " . $e->getMessage() . "\n";
}

// Test 3: Test Storage::disk('s3')->putFileAs directly
echo "\n3. Testing direct S3 upload:\n";

try {
    $uploadedFile3 = new UploadedFile(
        $tempFile,
        'direct-s3-test.png',
        'image/png',
        null,
        true
    );
    
    $directFilename = 'direct-test-' . time() . '.png';
    echo "   Testing Storage::disk('s3')->putFileAs('store/', \$file, '$directFilename')...\n";
    
    $directResult = Storage::disk('s3')->putFileAs('store/', $uploadedFile3, $directFilename);
    echo "   Direct S3 result: " . ($directResult ?: 'NULL/FALSE') . "\n";
    
    if ($directResult) {
        $exists = Storage::disk('s3')->exists($directResult);
        echo "   Direct file exists on S3: " . ($exists ? 'YES' : 'NO') . "\n";
        
        if ($exists) {
            echo "   Direct S3 URL: " . Storage::disk('s3')->url($directResult) . "\n";
            Storage::disk('s3')->delete($directResult);
            echo "   Cleaned up direct file\n";
        }
    }

} catch (Exception $e) {
    echo "   ❌ Direct S3 test failed: " . $e->getMessage() . "\n";
}

// Test 4: Check what getDisk() returns
echo "\n4. Storage Configuration Test:\n";
echo "   Helpers::getDisk(): " . Helpers::getDisk() . "\n";
echo "   Config filesystems.disks.s3 exists: " . (config('filesystems.disks.s3') ? 'YES' : 'NO') . "\n";

$s3Config = config('filesystems.disks.s3');
echo "   S3 Driver: " . ($s3Config['driver'] ?? 'NOT SET') . "\n";
echo "   S3 Endpoint: " . ($s3Config['endpoint'] ?? 'NOT SET') . "\n";

// Test 5: Test the actual business setting values
echo "\n5. Business Settings Raw Check:\n";
$localStorageRaw = DB::table('business_settings')->where('key', 'local_storage')->first();
$thirdPartyRaw = DB::table('business_settings')->where('key', '3rd_party_storage')->first();

echo "   local_storage in DB: " . ($localStorageRaw ? $localStorageRaw->value : 'NOT FOUND') . "\n";
echo "   3rd_party_storage in DB: " . ($thirdPartyRaw ? $thirdPartyRaw->value : 'NOT FOUND') . "\n";

echo "\n📋 DIAGNOSIS:\n";

if ($result && $result !== 'def.png' && isset($exists) && $exists) {
    echo "   ✅ Helpers::upload() works correctly\n";
} else {
    echo "   ❌ Helpers::upload() is failing\n";
}

if ($directResult && isset($exists) && $exists) {
    echo "   ✅ Direct S3 upload works\n";
} else {
    echo "   ❌ Direct S3 upload fails - check S3 credentials\n";
}

// Clean up
unlink($tempFile);
echo "\n=== TEST COMPLETE ===\n";