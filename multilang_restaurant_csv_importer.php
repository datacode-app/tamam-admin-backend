<?php

require_once 'vendor/autoload.php';

use App\Models\Store;
use App\Models\Vendor;
use App\Models\Translation;
use App\Models\BusinessSetting;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;

/**
 * Multi-Language Restaurant/Business CSV Importer
 * 
 * This script imports restaurants/businesses from CSV with multi-language support
 * 
 * CSV Format:
 * - Basic fields: id, vendor_email, phone, email, etc.
 * - Multi-language fields: name_en, name_ar, name_ckb, address_en, address_ar, address_ckb
 * - Images: logo_filename, cover_photo_filename
 * 
 * Image Handling:
 * - Place images in public/storage/restaurant_images/
 * - Use filename in CSV, script will copy to proper location
 * - Supports: jpg, jpeg, png, webp
 */

class MultiLanguageRestaurantCSVImporter
{
    private $supportedLanguages = ['en', 'ar', 'ckb'];
    private $defaultImagePath = 'public/storage/restaurant_images/';
    private $storageImagePath = 'restaurant/';
    private $importStats = [
        'processed' => 0,
        'imported' => 0,
        'failed' => 0,
        'translations_created' => 0,
        'images_processed' => 0
    ];

    public function __construct()
    {
        // Ensure image directory exists
        if (!file_exists(public_path('storage/restaurant_images'))) {
            mkdir(public_path('storage/restaurant_images'), 0755, true);
        }
    }

    public function importFromCSV($csvFilePath)
    {
        echo "🚀 Multi-Language Restaurant CSV Importer\n";
        echo "=========================================\n\n";

        if (!file_exists($csvFilePath)) {
            throw new Exception("CSV file not found: {$csvFilePath}");
        }

        try {
            $restaurants = (new FastExcel)->import($csvFilePath);
            echo "📁 Found " . count($restaurants) . " restaurants in CSV\n\n";

            $this->validateCSVStructure($restaurants);
            $this->processRestaurants($restaurants);
            $this->showImportSummary();

        } catch (Exception $e) {
            echo "❌ Import failed: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    private function validateCSVStructure($restaurants)
    {
        echo "🔍 Validating CSV structure...\n";

        $requiredFields = ['vendor_email', 'phone', 'name_en'];
        $sampleRow = reset($restaurants);

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $sampleRow)) {
                throw new Exception("Required field '{$field}' not found in CSV");
            }
        }

        // Check for multi-language fields
        $languageFields = [];
        foreach ($this->supportedLanguages as $lang) {
            if (array_key_exists("name_{$lang}", $sampleRow)) {
                $languageFields[] = "name_{$lang}";
            }
            if (array_key_exists("address_{$lang}", $sampleRow)) {
                $languageFields[] = "address_{$lang}";
            }
        }

        echo "   ✅ Required fields present\n";
        echo "   ✅ Multi-language fields found: " . implode(', ', $languageFields) . "\n";
        echo "   ✅ CSV structure is valid\n\n";
    }

    private function processRestaurants($restaurants)
    {
        echo "🏪 Processing restaurants...\n";

        DB::beginTransaction();

        try {
            foreach ($restaurants as $index => $restaurantData) {
                $this->importStats['processed']++;
                
                echo "   Processing row " . ($index + 1) . "... ";
                
                try {
                    $restaurant = $this->processSingleRestaurant($restaurantData);
                    $this->importStats['imported']++;
                    echo "✅ Success (ID: {$restaurant['id']})\n";
                } catch (Exception $e) {
                    $this->importStats['failed']++;
                    echo "❌ Failed: " . $e->getMessage() . "\n";
                    continue; // Continue with next restaurant
                }
            }

            DB::commit();
            echo "\n✅ All restaurants processed successfully!\n";

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Import failed during processing: " . $e->getMessage());
        }
    }

    private function processSingleRestaurant($data)
    {
        // 1. Find or create vendor
        $vendor = $this->getOrCreateVendor($data['vendor_email']);

        // 2. Prepare restaurant data
        $restaurantData = $this->prepareRestaurantData($data, $vendor->id);

        // 3. Create or update restaurant
        $restaurant = $this->createOrUpdateRestaurant($restaurantData);

        // 4. Process images
        $this->processImages($restaurant, $data);

        // 5. Create translations
        $this->createTranslations($restaurant, $data);

        return $restaurant;
    }

    private function getOrCreateVendor($email)
    {
        $vendor = Vendor::where('email', $email)->first();

        if (!$vendor) {
            // Create vendor with basic data
            $vendor = Vendor::create([
                'f_name' => 'Auto',
                'l_name' => 'Generated',
                'email' => $email,
                'phone' => '+000000000000', // Will be updated when store is created
                'password' => bcrypt('password123'),
                'status' => 1
            ]);

            echo "(Created vendor: {$email}) ";
        }

        return $vendor;
    }

    private function prepareRestaurantData($data, $vendorId)
    {
        return [
            'id' => $data['id'] ?? null,
            'vendor_id' => $vendorId,
            'name' => $data['name_en'], // Default English name
            'phone' => $data['phone'],
            'email' => $data['email'] ?? $data['vendor_email'],
            'address' => $data['address_en'] ?? 'Address not provided',
            'latitude' => $data['latitude'] ?? '0',
            'longitude' => $data['longitude'] ?? '0',
            'minimum_order' => $data['minimum_order'] ?? 0,
            'tax' => $data['tax'] ?? 0,
            'delivery_time' => $data['delivery_time'] ?? '30-45',
            'status' => isset($data['status']) ? ($data['status'] === 'active' ? 1 : 0) : 1,
            'zone_id' => $data['zone_id'] ?? 1,
            'module_id' => $data['module_id'] ?? 1,
            'comission' => $data['commission'] ?? 0,
            'schedule_order' => isset($data['schedule_order']) ? ($data['schedule_order'] === 'yes' ? 1 : 0) : 1,
            'free_delivery' => isset($data['free_delivery']) ? ($data['free_delivery'] === 'yes' ? 1 : 0) : 0,
            'delivery' => isset($data['delivery']) ? ($data['delivery'] === 'yes' ? 1 : 0) : 1,
            'take_away' => isset($data['take_away']) ? ($data['take_away'] === 'yes' ? 1 : 0) : 1,
            'veg' => isset($data['veg']) ? ($data['veg'] === 'yes' ? 1 : 0) : 1,
            'non_veg' => isset($data['non_veg']) ? ($data['non_veg'] === 'yes' ? 1 : 0) : 1,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }

    private function createOrUpdateRestaurant($restaurantData)
    {
        if (isset($restaurantData['id']) && $restaurantData['id']) {
            // Check if exists
            $existing = DB::table('stores')->where('id', $restaurantData['id'])->first();
            
            if ($existing) {
                DB::table('stores')->where('id', $restaurantData['id'])->update($restaurantData);
                return array_merge(['id' => $restaurantData['id']], $restaurantData);
            }
        }

        // Create new restaurant
        if (isset($restaurantData['id'])) {
            unset($restaurantData['id']); // Let database auto-increment
        }

        $id = DB::table('stores')->insertGetId($restaurantData);
        return array_merge(['id' => $id], $restaurantData);
    }

    private function processImages($restaurant, $data)
    {
        $imageFields = ['logo_filename', 'cover_photo_filename'];
        
        foreach ($imageFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $this->processImage($restaurant['id'], $field, $data[$field]);
                $this->importStats['images_processed']++;
            }
        }
    }

    private function processImage($restaurantId, $fieldType, $filename)
    {
        $sourcePath = public_path('storage/restaurant_images/' . $filename);
        
        if (!file_exists($sourcePath)) {
            echo "(Image not found: {$filename}) ";
            return null;
        }

        // Generate unique filename
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $newFilename = date('Y-m-d') . '-' . uniqid() . '.' . $extension;
        
        // Copy to storage
        $destinationPath = public_path('storage/restaurant/' . $newFilename);
        
        // Ensure destination directory exists
        if (!file_exists(dirname($destinationPath))) {
            mkdir(dirname($destinationPath), 0755, true);
        }

        if (copy($sourcePath, $destinationPath)) {
            // Update database
            $column = $fieldType === 'logo_filename' ? 'logo' : 'cover_photo';
            DB::table('stores')->where('id', $restaurantId)->update([
                $column => $newFilename
            ]);

            echo "(Image: {$filename} -> {$newFilename}) ";
            return $newFilename;
        }

        return null;
    }

    private function createTranslations($restaurant, $data)
    {
        // Clear existing translations
        DB::table('translations')
          ->where('translationable_type', 'App\Models\Store')
          ->where('translationable_id', $restaurant['id'])
          ->delete();

        foreach ($this->supportedLanguages as $lang) {
            $translations = [];

            // Name translation
            if (isset($data["name_{$lang}"]) && !empty($data["name_{$lang}"])) {
                $translations['name'] = $data["name_{$lang}"];
            }

            // Address translation
            if (isset($data["address_{$lang}"]) && !empty($data["address_{$lang}"])) {
                $translations['address'] = $data["address_{$lang}"];
            }

            // Description translation (if provided)
            if (isset($data["description_{$lang}"]) && !empty($data["description_{$lang}"])) {
                $translations['description'] = $data["description_{$lang}"];
            }

            // Insert translations
            foreach ($translations as $key => $value) {
                DB::table('translations')->insert([
                    'translationable_type' => 'App\Models\Store',
                    'translationable_id' => $restaurant['id'],
                    'locale' => $lang,
                    'key' => $key,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $this->importStats['translations_created']++;
            }
        }
    }

    private function showImportSummary()
    {
        echo "\n📊 Import Summary\n";
        echo "=================\n";
        echo "Processed: {$this->importStats['processed']}\n";
        echo "Imported: {$this->importStats['imported']}\n";
        echo "Failed: {$this->importStats['failed']}\n";
        echo "Translations created: {$this->importStats['translations_created']}\n";
        echo "Images processed: {$this->importStats['images_processed']}\n";

        if ($this->importStats['failed'] > 0) {
            echo "\n⚠️ Some restaurants failed to import. Check error messages above.\n";
        }

        echo "\n✅ Import completed successfully!\n";
        echo "\n📋 Next steps:\n";
        echo "1. Check imported restaurants in admin panel\n";
        echo "2. Test API endpoints with different locales\n";
        echo "3. Verify images are displaying correctly\n";
    }

    public function generateSampleCSV($outputPath = 'sample_restaurants_multilang.csv')
    {
        echo "📝 Generating sample CSV file...\n";

        $sampleData = [
            [
                'id' => '',
                'vendor_email' => 'kurdish.restaurant@test.com',
                'phone' => '+964771111001',
                'email' => 'kurdish.restaurant@test.com',
                'name_en' => 'Traditional Kurdish Kitchen',
                'name_ar' => 'المطبخ الكردي التقليدي',
                'name_ckb' => 'چێشتخانەی نەتەوەیی کوردی',
                'address_en' => '123 Kurdistan Street, Erbil',
                'address_ar' => 'شارع كردستان ١٢٣، أربيل',
                'address_ckb' => 'شەقامی کوردستان ١٢٣، هەولێر',
                'description_en' => 'Authentic Kurdish cuisine with traditional recipes',
                'description_ar' => 'المأكولات الكردية الأصيلة بالوصفات التقليدية',
                'description_ckb' => 'خواردنی کوردی ڕەسەن بە ڕەچەتە نەتەوەییەکان',
                'latitude' => '36.1911',
                'longitude' => '44.0093',
                'minimum_order' => '10.00',
                'tax' => '5.0',
                'commission' => '8.0',
                'delivery_time' => '30-45',
                'status' => 'active',
                'zone_id' => '1',
                'module_id' => '1',
                'schedule_order' => 'yes',
                'free_delivery' => 'no',
                'delivery' => 'yes',
                'take_away' => 'yes',
                'veg' => 'yes',
                'non_veg' => 'yes',
                'logo_filename' => 'kurdish_logo.jpg',
                'cover_photo_filename' => 'kurdish_cover.jpg'
            ],
            [
                'id' => '',
                'vendor_email' => 'arabic.delights@test.com',
                'phone' => '+964771111002',
                'email' => 'arabic.delights@test.com',
                'name_en' => 'Authentic Arabic Delights',
                'name_ar' => 'المأكولات العربية الأصيلة',
                'name_ckb' => 'خواردنە عەرەبییە ڕەسەنەکان',
                'address_en' => '456 Middle East Avenue, Baghdad',
                'address_ar' => 'شارع الشرق الأوسط ٤٥٦، بغداد',
                'address_ckb' => 'شەقامی ڕۆژهەڵاتی ناوەڕاست ٤٥٦، بەغدا',
                'description_en' => 'Traditional Arabic dishes with Mediterranean flavors',
                'description_ar' => 'الأطباق العربية التقليدية بنكهات البحر الأبيض المتوسط',
                'description_ckb' => 'خواردنە عەرەبییە نەتەوەیی بە تامی دەریای ناوەڕاست',
                'latitude' => '33.3152',
                'longitude' => '44.3661',
                'minimum_order' => '15.00',
                'tax' => '5.0',
                'commission' => '10.0',
                'delivery_time' => '20-35',
                'status' => 'active',
                'zone_id' => '1',
                'module_id' => '1',
                'schedule_order' => 'yes',
                'free_delivery' => 'yes',
                'delivery' => 'yes',
                'take_away' => 'yes',
                'veg' => 'yes',
                'non_veg' => 'yes',
                'logo_filename' => 'arabic_logo.jpg',
                'cover_photo_filename' => 'arabic_cover.jpg'
            ],
            [
                'id' => '',
                'vendor_email' => 'baghdad.grill@test.com',
                'phone' => '+964771111003',
                'email' => 'baghdad.grill@test.com',
                'name_en' => 'Baghdad Grill House',
                'name_ar' => 'مشاوي بغداد',
                'name_ckb' => 'ماڵی بریانی بەغدا',
                'address_en' => '789 Tigris Street, Baghdad',
                'address_ar' => 'شارع دجلة ٧٨٩، بغداد',
                'address_ckb' => 'شەقامی دجلە ٧٨٩، بەغدا',
                'description_en' => 'Best grilled meats and kebabs in the city',
                'description_ar' => 'أفضل اللحوم المشوية والكباب في المدينة',
                'description_ckb' => 'باشترین گۆشتی بریانکراو و کەباب لە شارەکە',
                'latitude' => '33.3128',
                'longitude' => '44.3615',
                'minimum_order' => '12.00',
                'tax' => '5.0',
                'commission' => '7.5',
                'delivery_time' => '25-40',
                'status' => 'active',
                'zone_id' => '1',
                'module_id' => '1',
                'schedule_order' => 'yes',
                'free_delivery' => 'no',
                'delivery' => 'yes',
                'take_away' => 'yes',
                'veg' => 'no',
                'non_veg' => 'yes',
                'logo_filename' => 'baghdad_logo.jpg',
                'cover_photo_filename' => 'baghdad_cover.jpg'
            ]
        ];

        $fastExcel = new FastExcel($sampleData);
        $fastExcel->export($outputPath);

        echo "✅ Sample CSV created: {$outputPath}\n";
        echo "📋 CSV includes:\n";
        echo "   - 3 sample restaurants\n";
        echo "   - Multi-language fields (en, ar, ku)\n";
        echo "   - Image filename references\n";
        echo "   - All required business fields\n\n";

        echo "🖼️ To use images:\n";
        echo "   1. Create folder: public/storage/restaurant_images/\n";
        echo "   2. Place your images there with filenames matching CSV\n";
        echo "   3. Supported formats: jpg, jpeg, png, webp\n\n";

        return $outputPath;
    }
}

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Main execution
if (isset($argv[1])) {
    $action = $argv[1];
    $importer = new MultiLanguageRestaurantCSVImporter();

    switch ($action) {
        case 'generate':
            $outputPath = $argv[2] ?? 'sample_restaurants_multilang.csv';
            $importer->generateSampleCSV($outputPath);
            echo "🎯 Usage: php multilang_restaurant_csv_importer.php import {$outputPath}\n";
            break;

        case 'import':
            $csvPath = $argv[2] ?? 'sample_restaurants_multilang.csv';
            $importer->importFromCSV($csvPath);
            break;

        default:
            echo "❌ Unknown action: {$action}\n";
            echo "Usage:\n";
            echo "  php multilang_restaurant_csv_importer.php generate [output_file.csv]\n";
            echo "  php multilang_restaurant_csv_importer.php import [input_file.csv]\n";
            exit(1);
    }
} else {
    echo "🚀 Multi-Language Restaurant CSV Importer\n";
    echo "=========================================\n\n";
    echo "Usage:\n";
    echo "  Generate sample CSV: php multilang_restaurant_csv_importer.php generate\n";
    echo "  Import from CSV:     php multilang_restaurant_csv_importer.php import sample_restaurants_multilang.csv\n\n";
    echo "CSV Format:\n";
    echo "  - Required: vendor_email, phone, name_en\n";
    echo "  - Multi-lang: name_[lang], address_[lang], description_[lang]\n";
    echo "  - Images: logo_filename, cover_photo_filename\n";
    echo "  - Business: minimum_order, tax, commission, delivery_time, etc.\n\n";
    echo "Supported languages: en (English), ar (Arabic), ku (Kurdish)\n";
}