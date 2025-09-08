<?php

require_once 'vendor/autoload.php';

use App\Models\Store;
use App\Models\Vendor;
use App\Models\Translation;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;

/**
 * Working Multi-Language Restaurant CSV Importer
 * 
 * This is a simplified, working version that you can easily use to import
 * restaurant data with multi-language support.
 */

class WorkingRestaurantImporter 
{
    private $supportedLanguages = ['en', 'ar', 'ckb'];
    private $stats = ['processed' => 0, 'imported' => 0, 'failed' => 0, 'translations' => 0, 'images' => 0];

    public function importFromCSV($csvPath)
    {
        echo "🚀 Restaurant Multi-Language CSV Import\n";
        echo "======================================\n\n";

        if (!file_exists($csvPath)) {
            die("❌ CSV file not found: {$csvPath}\n");
        }

        $restaurants = $this->readCSV($csvPath);
        
        if (empty($restaurants)) {
            die("❌ No data found in CSV file\n");
        }

        echo "📁 Found " . count($restaurants) . " restaurants to import\n\n";

        DB::beginTransaction();
        
        try {
            foreach ($restaurants as $index => $data) {
                $this->stats['processed']++;
                echo "Processing restaurant " . ($index + 1) . "... ";
                
                try {
                    $this->importSingleRestaurant($data);
                    $this->stats['imported']++;
                    echo "✅ Success\n";
                } catch (Exception $e) {
                    $this->stats['failed']++;
                    echo "❌ Failed: " . $e->getMessage() . "\n";
                }
            }

            DB::commit();
            $this->showStats();

        } catch (Exception $e) {
            DB::rollBack();
            echo "\n❌ Import failed: " . $e->getMessage() . "\n";
        }
    }

    private function readCSV($filePath)
    {
        $data = [];
        $handle = fopen($filePath, 'r');
        
        if ($handle === false) {
            throw new Exception("Cannot read CSV file");
        }

        // Get headers
        $headers = fgetcsv($handle);
        
        if ($headers === false) {
            throw new Exception("Cannot read CSV headers");
        }

        // Clean headers (remove BOM if present)
        $headers[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $headers[0]);

        echo "📋 CSV Headers: " . implode(', ', $headers) . "\n\n";

        // Read data rows
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }

        fclose($handle);
        return $data;
    }

    private function importSingleRestaurant($data)
    {
        // 1. Get or create vendor
        $vendor = $this->getOrCreateVendor($data['vendor_email']);

        // 2. Create restaurant
        $restaurantId = $this->createRestaurant($data, $vendor->id);

        // 3. Process images
        $this->processImages($restaurantId, $data);

        // 4. Create translations
        $this->createTranslations($restaurantId, $data);
    }

    private function getOrCreateVendor($email)
    {
        $vendor = Vendor::where('email', $email)->first();

        if (!$vendor) {
            $vendor = Vendor::create([
                'f_name' => 'Auto',
                'l_name' => 'Generated',
                'email' => $email,
                'phone' => '+964' . rand(100000000, 999999999),
                'password' => bcrypt('password123'),
                'status' => 1
            ]);
            echo "(New vendor created) ";
        }

        return $vendor;
    }

    private function createRestaurant($data, $vendorId)
    {
        $restaurantData = [
            'vendor_id' => $vendorId,
            'name' => $data['name_en'],
            'phone' => $data['phone'],
            'email' => $data['vendor_email'],
            'address' => $data['address_en'] ?? 'Address not provided',
            'latitude' => $data['latitude'] ?? '0',
            'longitude' => $data['longitude'] ?? '0',
            'minimum_order' => $data['minimum_order'] ?? 0,
            'tax' => $data['tax'] ?? 0,
            'comission' => $data['commission'] ?? 0,
            'delivery_time' => $data['delivery_time'] ?? '30-45',
            'status' => ($data['status'] ?? 'active') === 'active' ? 1 : 0,
            'zone_id' => $data['zone_id'] ?? 1,
            'module_id' => $data['module_id'] ?? 1,
            'schedule_order' => 1,
            'free_delivery' => 0,
            'delivery' => 1,
            'take_away' => 1,
            'veg' => 1,
            'non_veg' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ];

        return DB::table('stores')->insertGetId($restaurantData);
    }

    private function processImages($restaurantId, $data)
    {
        $imageFields = [
            'logo_filename' => 'logo',
            'cover_photo_filename' => 'cover_photo'
        ];

        foreach ($imageFields as $csvField => $dbColumn) {
            if (!empty($data[$csvField])) {
                $filename = $this->copyImage($data[$csvField]);
                if ($filename) {
                    DB::table('stores')->where('id', $restaurantId)->update([
                        $dbColumn => $filename
                    ]);
                    $this->stats['images']++;
                    echo "(Image: {$data[$csvField]}) ";
                }
            }
        }
    }

    private function copyImage($sourceFilename)
    {
        $sourcePath = public_path('storage/restaurant_images/' . $sourceFilename);
        
        if (!file_exists($sourcePath)) {
            return null;
        }

        // Generate unique filename
        $extension = pathinfo($sourceFilename, PATHINFO_EXTENSION);
        $newFilename = date('Y-m-d') . '-' . uniqid() . '.' . $extension;
        
        // Destination path
        $destinationDir = public_path('storage/restaurant/');
        if (!file_exists($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $destinationPath = $destinationDir . $newFilename;
        
        if (copy($sourcePath, $destinationPath)) {
            return $newFilename;
        }

        return null;
    }

    private function createTranslations($restaurantId, $data)
    {
        // Clear existing translations
        DB::table('translations')
          ->where('translationable_type', 'App\Models\Store')
          ->where('translationable_id', $restaurantId)
          ->delete();

        foreach ($this->supportedLanguages as $lang) {
            $translations = [];

            // Name translation
            if (!empty($data["name_{$lang}"])) {
                $translations['name'] = $data["name_{$lang}"];
            }

            // Address translation
            if (!empty($data["address_{$lang}"])) {
                $translations['address'] = $data["address_{$lang}"];
            }

            // Insert translations
            foreach ($translations as $key => $value) {
                DB::table('translations')->insert([
                    'translationable_type' => 'App\Models\Store',
                    'translationable_id' => $restaurantId,
                    'locale' => $lang,
                    'key' => $key,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $this->stats['translations']++;
            }
        }
    }

    private function showStats()
    {
        echo "\n📊 Import Summary\n";
        echo "=================\n";
        echo "Processed: {$this->stats['processed']}\n";
        echo "Imported: {$this->stats['imported']}\n";
        echo "Failed: {$this->stats['failed']}\n";
        echo "Translations: {$this->stats['translations']}\n";
        echo "Images: {$this->stats['images']}\n";
        
        echo "\n✅ Import completed!\n";
        echo "\n🎯 What to do next:\n";
        echo "1. Visit admin panel to see imported restaurants\n";
        echo "2. Test API: curl 'http://localhost:8000/api/v1/stores?locale=ar'\n";
        echo "3. Check translations work in different languages\n";
    }

    public function createSampleCSV($filename = 'restaurants_easy_import.csv')
    {
        echo "📝 Creating sample CSV: {$filename}\n";

        $handle = fopen($filename, 'w');
        
        // Headers
        fputcsv($handle, [
            'vendor_email', 'phone', 'name_en', 'name_ar', 'name_ckb',
            'address_en', 'address_ar', 'address_ckb',
            'latitude', 'longitude', 'minimum_order', 'tax', 'commission',
            'delivery_time', 'status', 'zone_id', 'module_id',
            'logo_filename', 'cover_photo_filename'
        ]);

        // Sample data
        $samples = [
            [
                'kurdish.food@test.com', '+964771000001', 'Kurdish Authentic Cuisine',
                'المطعم الكردي الأصيل', 'چێشتخانەی کوردی ڕاستەقینە',
                '100 Kurdish Street, Erbil', 'شارع كردي ١٠٠، أربيل', 'شەقامی کوردی ١٠٠، هەولێر',
                '36.1911', '44.0093', '10.00', '5.0', '8.0', '30-45', 'active', '1', '1',
                'kurdish_logo.jpg', 'kurdish_cover.jpg'
            ],
            [
                'arabian.nights@test.com', '+964771000002', 'Arabian Nights Restaurant',
                'مطعم ليالي العرب', 'چێشتخانەی شەوانی عەرەب',
                '200 Baghdad Avenue, Baghdad', 'شارع بغداد ٢٠٠، بغداد', 'شەقامی بەغدا ٢٠٠، بەغدا',
                '33.3152', '44.3661', '15.00', '5.0', '10.0', '25-40', 'active', '1', '1',
                'arabic_logo.jpg', 'arabic_cover.jpg'
            ],
            [
                'mesopotamian.grill@test.com', '+964771000003', 'Mesopotamian Grill',
                'مشاوي بلاد الرافدين', 'بریانگەی میزۆپۆتامیا',
                '300 Tigris Boulevard, Basra', 'شارع دجلة ٣٠٠، البصرة', 'بۆڵەڤاری دجلە ٣٠٠، بەسرە',
                '30.5085', '47.7804', '8.00', '5.0', '6.0', '20-30', 'active', '1', '1',
                'mesopotamian_logo.jpg', 'mesopotamian_cover.jpg'
            ]
        ];

        foreach ($samples as $sample) {
            fputcsv($handle, $sample);
        }

        fclose($handle);

        echo "✅ Sample CSV created with 3 restaurants\n";
        echo "🖼️ Don't forget to place corresponding images in: public/storage/restaurant_images/\n";
        echo "📋 To import: php restaurant_csv_import_working.php import {$filename}\n";
    }
}

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Command line interface
if (isset($argv[1])) {
    $action = $argv[1];
    $importer = new WorkingRestaurantImporter();

    switch ($action) {
        case 'sample':
            $filename = $argv[2] ?? 'restaurants_easy_import.csv';
            $importer->createSampleCSV($filename);
            break;

        case 'import':
            $filename = $argv[2] ?? 'restaurants_easy_import.csv';
            $importer->importFromCSV($filename);
            break;

        default:
            echo "❌ Unknown action: {$action}\n\n";
            echo "Usage:\n";
            echo "  php restaurant_csv_import_working.php sample [filename.csv]  # Create sample CSV\n";
            echo "  php restaurant_csv_import_working.php import [filename.csv]  # Import restaurants\n";
            exit(1);
    }
} else {
    echo "🚀 Easy Multi-Language Restaurant CSV Importer\n";
    echo "==============================================\n\n";
    echo "This tool lets you easily import restaurants with multi-language data:\n\n";
    echo "📝 Step 1: Create sample CSV\n";
    echo "   php restaurant_csv_import_working.php sample\n\n";
    echo "🖼️ Step 2: Add images (optional)\n";
    echo "   Place images in: public/storage/restaurant_images/\n";
    echo "   Use filenames from CSV (kurdish_logo.jpg, etc.)\n\n";
    echo "📥 Step 3: Import data\n";
    echo "   php restaurant_csv_import_working.php import restaurants_easy_import.csv\n\n";
    echo "🌍 Supported languages: English, Arabic, Kurdish Sorani\n";
}