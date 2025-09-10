<?php
/**
 * Fix the actual upload issue - ensure files are uploaded to S3
 * Run: php real_upload_fix.php
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Store;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

echo "=== FIXING ACTUAL UPLOAD ISSUE ===\n";

echo "\n🔍 PROBLEM IDENTIFIED:\n";
echo "Files are being 'uploaded' (database updated) but not actually saved to S3\n";
echo "This happens because:\n";
echo "1. Helpers::upload() succeeds in test but fails in real form uploads\n";
echo "2. VendorController shows 'success' even when file isn't uploaded\n";
echo "3. Empty catch blocks hide the actual errors\n";

echo "\n🛠️ SOLUTION: Add Better Error Handling and Logging\n";

// Step 1: Create a better upload method with proper error handling
echo "\n1. Creating Enhanced Upload Method...\n";

$enhancedUploadMethod = '
    public static function uploadWithErrorHandling($dir, $format, $image = null)
    {
        if ($image == null) {
            return "def.png";
        }

        try {
            $imageName = \Carbon\Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
            $disk = self::getDisk();
            
            \Log::info("Starting upload", [
                "disk" => $disk,
                "dir" => $dir,
                "filename" => $imageName,
                "original_name" => $image->getClientOriginalName(),
                "size" => $image->getSize()
            ]);

            if (!Storage::disk($disk)->exists($dir)) {
                Storage::disk($disk)->makeDirectory($dir);
                \Log::info("Created directory: " . $dir);
            }

            $result = Storage::disk($disk)->putFileAs($dir, $image, $imageName);
            
            if ($result) {
                \Log::info("Upload successful", ["path" => $result]);
                return $imageName;
            } else {
                \Log::error("Upload failed - putFileAs returned false");
                return "def.png";
            }

        } catch (\Exception $e) {
            \Log::error("Upload exception", [
                "error" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
                "trace" => $e->getTraceAsString()
            ]);
            return "def.png";
        }
    }
';

echo "   ✅ Enhanced upload method created (see output below)\n";

// Step 2: Check recent failed uploads
echo "\n2. Checking Recent Upload Attempts...\n";

$recentStores = Store::where('updated_at', '>=', now()->subHours(24))
                   ->whereNotNull('logo')
                   ->where('logo', '!=', 'def.png')
                   ->get();

echo "   Recent stores with logos: " . $recentStores->count() . "\n";

foreach ($recentStores as $store) {
    $logoPath = 'store/' . $store->logo;
    $existsOnS3 = Storage::disk('s3')->exists($logoPath);
    $status = $existsOnS3 ? '✅' : '❌';
    
    echo "   $status Store ID {$store->id}: {$store->logo} (Updated: {$store->updated_at})\n";
    
    if (!$existsOnS3) {
        echo "      Missing file: $logoPath\n";
    }
}

// Step 3: Attempt to fix missing files by re-checking local storage
echo "\n3. Checking for Files in Wrong Storage Location...\n";

foreach ($recentStores as $store) {
    if ($store->logo && $store->logo !== 'def.png') {
        $logoPath = 'store/' . $store->logo;
        $existsOnS3 = Storage::disk('s3')->exists($logoPath);
        $existsOnPublic = Storage::disk('public')->exists($logoPath);
        
        if (!$existsOnS3 && $existsOnPublic) {
            echo "   📁 Store ID {$store->id}: File found on public storage, migrating to S3...\n";
            
            try {
                $fileContent = Storage::disk('public')->get($logoPath);
                $migrated = Storage::disk('s3')->put($logoPath, $fileContent);
                
                if ($migrated) {
                    echo "      ✅ Successfully migrated to S3\n";
                    // Verify
                    $nowExists = Storage::disk('s3')->exists($logoPath);
                    echo "      Verification: " . ($nowExists ? 'PASS' : 'FAIL') . "\n";
                } else {
                    echo "      ❌ Migration failed\n";
                }
            } catch (Exception $e) {
                echo "      ❌ Migration error: " . $e->getMessage() . "\n";
            }
        } elseif (!$existsOnS3 && !$existsOnPublic) {
            echo "   ❌ Store ID {$store->id}: File missing from both storages - upload truly failed\n";
        }
    }
}

// Step 4: Test current upload functionality
echo "\n4. Testing Current Upload System...\n";

// Create test data
$testContent = 'test upload ' . time();
$tempFile = tempnam(sys_get_temp_dir(), 'upload_test');
file_put_contents($tempFile, $testContent);

$testUpload = new \Illuminate\Http\UploadedFile(
    $tempFile,
    'test-current-upload.png',
    'image/png',
    null,
    true
);

try {
    echo "   Testing current Helpers::upload()...\n";
    $result = Helpers::upload('store/', 'png', $testUpload);
    echo "   Result: $result\n";
    
    if ($result && $result !== 'def.png') {
        $testPath = 'store/' . $result;
        $testExists = Storage::disk('s3')->exists($testPath);
        echo "   File exists on S3: " . ($testExists ? 'YES' : 'NO') . "\n";
        
        if ($testExists) {
            Storage::disk('s3')->delete($testPath);
            echo "   Test file cleaned up\n";
        }
    }
} catch (Exception $e) {
    echo "   Upload test failed: " . $e->getMessage() . "\n";
}

unlink($tempFile);

echo "\n📋 IMMEDIATE FIXES NEEDED:\n";

echo "\n1. Replace empty catch blocks in Helpers::upload() with proper logging\n";
echo "2. Add validation before upload to ensure file is valid\n"; 
echo "3. Add verification after upload to ensure file was saved\n";
echo "4. Update VendorController to check upload success before saving to database\n";

echo "\n🔧 ENHANCED UPLOAD METHOD TO ADD TO helpers.php:\n";
echo $enhancedUploadMethod;

echo "\n💡 QUICK FIX FOR TESTING:\n";
echo "Add this to the beginning of VendorController update method:\n";
echo '
if ($request->hasFile("logo")) {
    $file = $request->file("logo");
    \Log::info("Logo upload attempt", [
        "original_name" => $file->getClientOriginalName(),
        "size" => $file->getSize(),
        "mime" => $file->getMimeType(),
        "tmp_name" => $file->getPathname()
    ]);
}
';

echo "\n=== ANALYSIS COMPLETE ===\n";