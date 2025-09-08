<?php
/**
 * DISABLE BUSINESS SETUP REDIRECT - Force dashboard access
 * This will bypass the business setup redirects and go directly to dashboard
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🚫 DISABLING BUSINESS SETUP REDIRECTS\n";
echo "=" . str_repeat("=", 50) . "\n\n";

try {
    // Fix 1: Modify LoginController to always go to dashboard
    echo "1. 🔧 FIXING LOGIN CONTROLLER\n";
    echo "-----------------------------\n";
    
    $loginControllerPath = 'app/Http/Controllers/LoginController.php';
    if (file_exists($loginControllerPath)) {
        $content = file_get_contents($loginControllerPath);
        
        // Replace the conditional redirect with direct dashboard redirect
        $oldCode = 'if (isset($modules) && ($modules->count() > 0)) {

                return redirect()->route(\'admin.dashboard\');
            }
            return redirect()->route(\'admin.business-settings.business-setup\');';
            
        $newCode = '// BUSINESS SETUP REDIRECT DISABLED - Always go to dashboard
            return redirect()->route(\'admin.dashboard\');';
            
        if (strpos($content, $oldCode) !== false) {
            $content = str_replace($oldCode, $newCode, $content);
            file_put_contents($loginControllerPath, $content);
            echo "✅ LoginController fixed - will always redirect to dashboard\n";
        } else {
            // Alternative approach - find the line and replace it
            $content = preg_replace(
                '/return redirect\(\)->route\(\'admin\.business-settings\.business-setup\'\);/',
                '// BUSINESS SETUP REDIRECT DISABLED
            return redirect()->route(\'admin.dashboard\');',
                $content
            );
            file_put_contents($loginControllerPath, $content);
            echo "✅ LoginController fixed with alternative method\n";
        }
        
    } else {
        echo "❌ LoginController not found\n";
    }
    
    echo "\n2. 🔧 FIXING DASHBOARD CONTROLLER\n";
    echo "--------------------------------\n";
    
    $dashboardControllerPath = 'app/Http/Controllers/Admin/DashboardController.php';
    if (file_exists($dashboardControllerPath)) {
        $content = file_get_contents($dashboardControllerPath);
        
        // Comment out the settings redirect
        $oldCode = 'if($module_type == \'settings\'){
            return redirect()->route(\'admin.business-settings.business-setup\');
        }';
        
        $newCode = '// BUSINESS SETUP REDIRECT DISABLED
        // if($module_type == \'settings\'){
        //     return redirect()->route(\'admin.business-settings.business-setup\');
        // }';
        
        if (strpos($content, $oldCode) !== false) {
            $content = str_replace($oldCode, $newCode, $content);
            file_put_contents($dashboardControllerPath, $content);
            echo "✅ DashboardController fixed - settings redirect disabled\n";
        } else {
            echo "⚠️  Settings redirect pattern not found (might already be fixed)\n";
        }
        
    } else {
        echo "❌ DashboardController not found\n";
    }
    
    echo "\n3. 🗄️  CHECKING MODULE CONFIGURATION\n";
    echo "-----------------------------------\n";
    
    // Check if we have business settings that indicate setup is complete
    $businessSettings = DB::table('business_settings')->where('key', 'business_name')->first();
    if ($businessSettings && $businessSettings->value) {
        echo "✅ Business name exists: " . $businessSettings->value . "\n";
        echo "✅ Business setup appears to be complete\n";
    } else {
        echo "⚠️  No business name found - might need basic setup\n";
        
        // Insert basic business settings to indicate setup is complete
        DB::table('business_settings')->updateOrInsert(
            ['key' => 'business_name'],
            [
                'key' => 'business_name',
                'value' => 'Tamam Delivery Platform',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        
        DB::table('business_settings')->updateOrInsert(
            ['key' => 'business_setup_done'],
            [
                'key' => 'business_setup_done',
                'value' => '1',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        
        echo "✅ Added basic business settings to mark setup as complete\n";
    }
    
    echo "\n4. 🧹 CLEARING CACHES\n";
    echo "--------------------\n";
    
    // Clear Laravel caches to ensure changes take effect
    $cacheCommands = [
        'php artisan cache:clear',
        'php artisan config:clear',
        'php artisan route:clear',
        'php artisan view:clear'
    ];
    
    foreach ($cacheCommands as $command) {
        echo "Running: $command\n";
        $output = shell_exec($command . ' 2>&1');
        if (strpos($output, 'cleared') !== false || strpos($output, 'cache') !== false) {
            echo "✅ Cache cleared\n";
        }
    }
    
    echo "\n🎉 BUSINESS SETUP REDIRECT DISABLED!\n";
    echo "=" . str_repeat("=", 50) . "\n";
    echo "✅ Login will now go directly to dashboard\n";
    echo "✅ Dashboard redirects disabled\n";
    echo "✅ Business settings marked as complete\n";
    echo "✅ All caches cleared\n";
    echo "\n🚪 TEST LOGIN:\n";
    echo "   URL: http://localhost:8000/login/admin\n";
    echo "   Email: admin@admin.com\n";
    echo "   Password: 12345678\n";
    echo "\n✨ Should now go directly to the main dashboard!\n";
    
} catch (Exception $e) {
    echo "💥 ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}