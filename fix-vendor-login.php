<?php

echo "🔧 Fixing Vendor Login DataSettings Issue\n";
echo "==========================================\n";

// Include Laravel bootstrap
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DataSetting;

try {
    echo "📊 Current DataSettings for login URLs:\n";
    $currentSettings = DataSetting::whereIn('key', [
        'store_login_url', 
        'store_employee_login_url', 
        'admin_login_url', 
        'admin_employee_login_url'
    ])->get();
    
    foreach($currentSettings as $setting) {
        echo "  {$setting->key} => {$setting->value}\n";
    }
    
    echo "\n📝 Setting required DataSettings values...\n";
    
    // Set the required DataSetting values for login URLs
    // CRITICAL: store_login_url must be 'vendor' for VendorMiddleware to work
    $settings = [
        ['key' => 'admin_login_url', 'value' => 'admin', 'type' => 'login_admin'],
        ['key' => 'store_login_url', 'value' => 'vendor', 'type' => 'login_store'],
        ['key' => 'admin_employee_login_url', 'value' => 'admin_employee', 'type' => 'login_admin_employee'],
        ['key' => 'store_employee_login_url', 'value' => 'store_employee', 'type' => 'login_store_employee']
    ];
    
    echo "🎯 IMPORTANT: Setting store_login_url = 'vendor' so /login/vendor works\n";
    
    foreach($settings as $setting) {
        DataSetting::updateOrCreate(
            ['key' => $setting['key']],
            [
                'value' => $setting['value'],
                'type' => $setting['type'],
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        echo "  ✅ Set {$setting['key']} => {$setting['value']}\n";
    }
    
    echo "\n🔍 Final DataSettings values:\n";
    $finalSettings = DataSetting::whereIn('key', [
        'store_login_url', 
        'store_employee_login_url', 
        'admin_login_url', 
        'admin_employee_login_url'
    ])->get();
    
    foreach($finalSettings as $setting) {
        echo "  {$setting->key} => {$setting->value}\n";
    }
    
    echo "\n✅ DataSettings fix completed successfully!\n";
    echo "   Vendor login should now work at: /login/vendor\n";
    
    echo "\n🧪 Testing the fix...\n";
    
    // Test the helper function
    try {
        $vendorLoginUrl = \App\CentralLogics\Helpers::get_login_url('store_login_url');
        echo "  ✅ Helpers::get_login_url('store_login_url') = '$vendorLoginUrl'\n";
        
        if ($vendorLoginUrl === 'vendor') {
            echo "  ✅ Perfect! VendorMiddleware will redirect to /login/vendor\n";
        } else {
            echo "  ⚠️  WARNING: Expected 'vendor', got '$vendorLoginUrl'\n";
        }
    } catch (\Exception $e) {
        echo "  ❌ Error testing helper: " . $e->getMessage() . "\n";
    }
    
    echo "\n📋 Next steps:\n";
    echo "  1. VendorMiddleware will now redirect to proper login page\n";
    echo "  2. Vendor panel should be accessible after login\n";
    echo "  3. Test URL: https://admin-stag.tamam.krd/vendor-panel/business-settings/store-setup\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
    exit(1);
}