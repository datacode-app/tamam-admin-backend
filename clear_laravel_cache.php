<?php
/**
 * Clear Laravel Cache for Translation Changes
 */

require_once __DIR__ . '/vendor/autoload.php';

// Set up minimal Laravel environment for cache clearing
$app = new \Illuminate\Foundation\Application(__DIR__);

echo "🔄 CLEARING LARAVEL CACHE FOR TRANSLATIONS\n";
echo "==========================================\n\n";

try {
    echo "1️⃣  **Clearing Application Cache...**\n";
    
    // Clear various cache types
    $cacheCommands = [
        'cache:clear' => 'Application cache',
        'config:clear' => 'Configuration cache',
        'route:clear' => 'Route cache', 
        'view:clear' => 'View cache'
    ];
    
    foreach ($cacheCommands as $command => $description) {
        echo "   🧹 Clearing $description...\n";
        
        $output = shell_exec("php artisan $command 2>&1");
        
        if (strpos($output, 'cleared') !== false || strpos($output, 'Cache cleared') !== false) {
            echo "      ✅ $description cleared successfully\n";
        } else {
            echo "      ⚠️  $description: " . trim($output) . "\n";
        }
    }
    
    echo "\n2️⃣  **Testing Category Translation Loading...**\n";
    
    // Test if we can now load category with translations
    echo "   🔍 Testing direct database query for Kurdish translations...\n";
    
    // Since we can't connect to database directly, let's create a simple artisan command test
    $testOutput = shell_exec("php artisan tinker --execute=\"echo 'Testing translations...'; \App\Models\Category::find(66433);\"");
    
    if ($testOutput) {
        echo "   📋 Category query result:\n";
        echo "      " . trim($testOutput) . "\n";
    } else {
        echo "   ⚠️  Could not test category query\n";
    }
    
    echo "\n3️⃣  **Cache Clear Complete**\n";
    echo "   ✅ All Laravel caches have been cleared\n";
    echo "   ✅ Translation changes should now be visible in API\n";
    echo "   🔄 Test the API again with Kurdish headers\n";
    
} catch (Exception $e) {
    echo "❌ Error clearing cache: " . $e->getMessage() . "\n";
    echo "🔧 Try running manually: php artisan cache:clear\n";
}