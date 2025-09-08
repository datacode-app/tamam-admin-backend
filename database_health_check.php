#!/usr/bin/env php
<?php
// Auto-generated monitoring script
require_once "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 Database Health Check - " . date("Y-m-d H:i:s") . "\n";

$testQueries = [
    "SELECT COUNT(*) FROM orders WHERE delivery_man_id IS NULL",
    "SELECT COUNT(*) FROM orders WHERE schedule_at IS NOT NULL", 
    "SELECT COUNT(*) FROM orders WHERE created_at <> schedule_at"
];

$healthy = true;
foreach ($testQueries as $query) {
    try {
        DB::select($query);
        echo "✅ Query OK: " . substr($query, 0, 50) . "...\n";
    } catch (Exception $e) {
        echo "❌ Query FAILED: " . $e->getMessage() . "\n";
        $healthy = false;
    }
}

echo $healthy ? "🎉 Database is healthy!\n" : "⚠️ Database needs attention!\n";
exit($healthy ? 0 : 1);
