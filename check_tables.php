<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

$modelFiles = File::allFiles(app_path('Models'));
$modelTables = [];

foreach ($modelFiles as $file) {
    $className = 'App\\Models\\' . str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());
    if (class_exists($className)) {
        try {
            $model = new $className;
            $modelTables[] = $model->getTable();
        } catch (Exception $e) {
            continue;
        }
    }
}

$modelTables = array_unique($modelTables);
sort($modelTables);

$existingTables = [];
foreach (DB::select('SHOW TABLES') as $table) {
    $existingTables[] = array_values((array) $table)[0];
}
sort($existingTables);

$missingTables = array_diff($modelTables, $existingTables);

echo "Total model tables: " . count($modelTables) . PHP_EOL;
echo "Existing tables: " . count($existingTables) . PHP_EOL;
echo "Missing tables: " . count($missingTables) . PHP_EOL;

if (!empty($missingTables)) {
    echo "Missing tables:" . PHP_EOL;
    foreach ($missingTables as $table) {
        echo "  - $table" . PHP_EOL;
    }
}

echo "\nSample of existing tables:" . PHP_EOL;
foreach (array_slice($existingTables, 0, 10) as $table) {
    echo "  - $table" . PHP_EOL;
}