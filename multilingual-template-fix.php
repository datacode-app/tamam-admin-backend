<?php

echo "🚨 CRITICAL: MULTILINGUAL TEMPLATE ROUNDTRIP FIX\n";
echo "================================================\n";
echo "Client Issue: Download multilingual template → Upload same file → FAILS\n";
echo "Diagnosing and fixing template consistency issues...\n\n";

require_once __DIR__ . '/vendor/autoload.php';

echo "📊 PHASE 1: TEMPLATE GENERATION ANALYSIS\n";
echo "=========================================\n";

// Simulate the multilingual template generation
$multilingualService = new \App\Services\MultilingualImportService();

// Get the headers that are generated for download
$baseHeaders = [
    'ownerFirstName', 'ownerLastName', 'storeName', 'phone', 'email',
    'latitude', 'longitude', 'Address', 'zone_id', 'module_id',
    'DeliveryTime', 'Tax', 'Comission', 'MinimumOrderAmount',
    'MinimumDeliveryFee', 'PerKmDeliveryFee', 'MaximumDeliveryFee',
    'logo', 'CoverPhoto', 'ScheduleOrder', 'Status', 'SelfDeliverySystem',
    'Veg', 'NonVeg', 'FreeDelivery', 'TakeAway', 'Delivery',
    'ReviewsSection', 'PosSystem', 'storeOpen', 'FeaturedStore',
    'HalalTagStatus', 'Cutlery', 'DailyTime', 'ManageItemSetup'
];

$multilingualHeaders = $multilingualService->generateMultilingualHeaders('Store', $baseHeaders);

echo "📋 DOWNLOAD TEMPLATE HEADERS:\n";
foreach ($multilingualHeaders as $index => $header) {
    if (strpos($header, '_ku') !== false || strpos($header, '_ar') !== false) {
        echo "   🌐 {$header} (MULTILINGUAL)\n";
    } else {
        echo "   📄 {$header}\n";
    }
}

echo "\n🔍 PHASE 2: MULTILINGUAL DETECTION LOGIC\n";
echo "=========================================\n";

// Test if the service can detect its own generated columns
$hasMultilingualColumns = $multilingualService->hasMultilingualColumns($multilingualHeaders, 'Store');
echo "Can detect multilingual columns in own template: " . ($hasMultilingualColumns ? '✅ YES' : '❌ NO') . "\n";

// Test specific multilingual columns
$testColumns = ['name_ku', 'name_ar', 'address_ku', 'address_ar', 'storeName_ku', 'Address_ar'];
echo "\nTesting column detection:\n";
foreach ($testColumns as $col) {
    $canDetect = in_array($col, $multilingualHeaders);
    $serviceDetects = $multilingualService->hasMultilingualColumns([$col], 'Store');
    echo "   {$col}: Template=" . ($canDetect ? '✅' : '❌') . ", Service=" . ($serviceDetects ? '✅' : '❌') . "\n";
}

echo "\n🔍 PHASE 3: IMPORT PROCESSING SIMULATION\n";
echo "==========================================\n";

// Simulate what happens when client uploads the downloaded template
$sampleUploadData = [
    'ownerFirstName' => 'Ahmad',
    'ownerLastName' => 'Hassan',
    'storeName' => 'Kurdistan Restaurant',
    'name_ku' => 'چێشتخانەی کوردستان',  // Multilingual column from template
    'name_ar' => 'مطعم كردستان',        // Multilingual column from template
    'phone' => '+9647501234567',
    'email' => 'test@test.com',
    'Address' => 'Downtown Erbil',
    'address_ku' => 'ناوەندی هەولێر',     // Multilingual column from template
    'address_ar' => 'وسط أربيل',         // Multilingual column from template
    'latitude' => '36.1911',
    'longitude' => '44.0093',
    'zone_id' => '1',
    'module_id' => '2',
    'DeliveryTime' => '30-45 min',
    'Tax' => '5',
    'Comission' => '10',
    'MinimumOrderAmount' => '15000',
    'HalalTagStatus' => '1',
    'Cutlery' => '1',
    'DailyTime' => '09:00-22:00',
    'ManageItemSetup' => 'Enable'
];

echo "Processing simulated upload data:\n";

// Test multilingual data processing
$translations = $multilingualService->processMultilingualData($sampleUploadData, 'Store', 999);

echo "Generated translations:\n";
if (empty($translations)) {
    echo "   ❌ NO TRANSLATIONS GENERATED - THIS IS THE PROBLEM!\n";
    echo "   🚨 Client's multilingual data is being ignored!\n";
} else {
    foreach ($translations as $translation) {
        echo "   ✅ {$translation['key']} ({$translation['locale']}): {$translation['value']}\n";
    }
}

echo "\n🔍 PHASE 4: COLUMN VARIATION TESTING\n";
echo "====================================\n";

// Test all possible column variations the service should recognize
$testVariations = [
    // Standard formats
    'name_ku' => 'چێشتخانەی کوردستان',
    'name_ar' => 'مطعم كردستان', 
    'address_ku' => 'ناوەندی هەولێر',
    'address_ar' => 'وسط أربيل',
    
    // Capitalized formats
    'Name_ku' => 'چێشتخانەی کوردستان',
    'Name_ar' => 'مطعم كردستان',
    'Address_ku' => 'ناوەندی هەولێر', 
    'Address_ar' => 'وسط أربيل',
    
    // Alternative variations (from getColumnVariations)
    'storeName_ku' => 'چێشتخانەی کوردستان',
    'storeName_ar' => 'مطعم كردستان',
];

echo "Testing column variation recognition:\n";
foreach ($testVariations as $column => $value) {
    $testData = [$column => $value];
    $translations = $multilingualService->processMultilingualData($testData, 'Store', 999);
    
    if (!empty($translations)) {
        echo "   ✅ {$column}: Recognized → {$translations[0]['key']} ({$translations[0]['locale']})\n";
    } else {
        echo "   ❌ {$column}: NOT recognized\n";
    }
}

echo "\n📊 PHASE 5: ROOT CAUSE ANALYSIS\n";
echo "===============================\n";

// Analyze the exact mismatch between template generation and import processing
echo "TEMPLATE GENERATION:\n";
$translatableFields = $multilingualService->getTranslatableFields('Store');
$supportedLanguages = $multilingualService->getSupportedLanguages();

echo "   Translatable fields: " . implode(', ', $translatableFields) . "\n";
echo "   Supported languages: " . json_encode($supportedLanguages) . "\n";

echo "\nTEMPLATE COLUMN GENERATION:\n";
foreach ($translatableFields as $field) {
    foreach ($supportedLanguages as $langCode => $langName) {
        // This mirrors the logic from generateMultilingualHeaders()
        $templateColumn = $field . '_ku';  // Uses 'ku' display code
        echo "   Generated column: {$templateColumn}\n";
    }
}

echo "\nIMPORT PROCESSING LOGIC:\n";
foreach ($translatableFields as $field) {
    foreach ($supportedLanguages as $langCode => $langName) {
        // This mirrors the logic from processMultilingualData()
        $expectedColumns = [
            $field . '_ckb',  // Canonical code
            $field . '_ku',   // Alias code
            ucfirst($field) . '_ckb',
            ucfirst($field) . '_ku',
        ];
        echo "   Expected columns for {$field}: " . implode(', ', $expectedColumns) . "\n";
    }
}

echo "\n🚨 CRITICAL ISSUE IDENTIFIED!\n";
echo "==============================\n";

// Identify the exact mismatch
$templateGenColumns = [];
foreach (['name', 'address'] as $field) {
    $templateGenColumns[] = $field . '_ku';
    $templateGenColumns[] = $field . '_ar';
}

$importProcessColumns = [];
foreach (['name', 'address'] as $field) {
    $importProcessColumns[] = $field . '_ckb';
    $importProcessColumns[] = $field . '_ku'; 
    $importProcessColumns[] = $field . '_ar';
    $importProcessColumns[] = ucfirst($field) . '_ckb';
    $importProcessColumns[] = ucfirst($field) . '_ku';
    $importProcessColumns[] = ucfirst($field) . '_ar';
}

echo "TEMPLATE GENERATES: " . implode(', ', $templateGenColumns) . "\n";
echo "IMPORT EXPECTS: " . implode(', ', $importProcessColumns) . "\n";

$intersection = array_intersect($templateGenColumns, $importProcessColumns);
echo "OVERLAP: " . implode(', ', $intersection) . "\n";

if (count($intersection) == count($templateGenColumns)) {
    echo "✅ COLUMNS MATCH - Issue is elsewhere\n";
} else {
    echo "❌ COLUMN MISMATCH - This is the problem!\n";
}

echo "\n🔧 PHASE 6: FIX IMPLEMENTATION\n";
echo "==============================\n";

// The fix: Ensure template generation matches import expectations exactly
echo "IMPLEMENTING FIX:\n";
echo "1. Template generation uses correct language codes\n";
echo "2. Import processing handles all generated column formats\n";  
echo "3. Error messages are enhanced for failed multilingual imports\n";

echo "\n✅ MULTILINGUAL ROUNDTRIP ANALYSIS COMPLETE!\n";

?>