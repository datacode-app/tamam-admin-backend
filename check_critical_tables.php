<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// List of critical models that are likely used in the API
$criticalModels = [
    'Currency',
    'BusinessSetting', 
    'DataSetting',
    'Module',
    'Zone',
    'Category',
    'Item',
    'Store',
    'User',
    'Order',
    'DeliveryMan',
    'Admin',
    'OfflinePaymentMethod',
    'OfflinePayments',
    'SocialMedia',
];

$existingTables = [];
foreach (DB::select('SHOW TABLES') as $table) {
    $existingTables[] = array_values((array) $table)[0];
}

$missingTables = [];
$existingCriticalTables = [];

foreach ($criticalModels as $modelName) {
    $className = "App\\Models\\$modelName";
    if (class_exists($className)) {
        try {
            $model = new $className;
            $tableName = $model->getTable();
            
            if (in_array($tableName, $existingTables)) {
                $existingCriticalTables[] = $tableName;
            } else {
                $missingTables[] = $tableName;
            }
        } catch (Exception $e) {
            echo "Error with $modelName: " . $e->getMessage() . PHP_EOL;
        }
    } else {
        echo "Model $modelName does not exist" . PHP_EOL;
    }
}

echo "=== CRITICAL TABLE ANALYSIS ===" . PHP_EOL;
echo "Total existing tables in DB: " . count($existingTables) . PHP_EOL;
echo "Critical tables found: " . count($existingCriticalTables) . PHP_EOL;
echo "Missing critical tables: " . count($missingTables) . PHP_EOL;

if (!empty($missingTables)) {
    echo "\nMISSING CRITICAL TABLES:" . PHP_EOL;
    foreach ($missingTables as $table) {
        echo "  ❌ $table" . PHP_EOL;
    }
}

echo "\nEXISTING CRITICAL TABLES:" . PHP_EOL;
foreach ($existingCriticalTables as $table) {
    echo "  ✅ $table" . PHP_EOL;
}