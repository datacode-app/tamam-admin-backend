<?php

/**
 * Admin Login Diagnostic Tool
 * Run this to diagnose login issues
 */

require_once 'vendor/autoload.php';

echo "🔍 Admin Login Diagnostic Tool\n";
echo "================================\n\n";

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

echo "1. Database Connection Test:\n";
try {
    $dbName = DB::connection()->getDatabaseName();
    echo "   ✅ Connected to database: $dbName\n";
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n2. Admin Table Check:\n";
try {
    if (Schema::hasTable('admins')) {
        echo "   ✅ Admins table exists\n";
        $adminCount = Admin::count();
        echo "   📊 Total admin users: $adminCount\n";
        
        if ($adminCount === 0) {
            echo "   ⚠️  No admin users found - creating default admin...\n";
            $admin = new Admin();
            $admin->f_name = 'Admin';
            $admin->l_name = 'User';
            $admin->phone = '1234567890';
            $admin->email = 'admin@admin.com';
            $admin->password = Hash::make('12345678');
            $admin->save();
            echo "   ✅ Default admin created: admin@admin.com / 12345678\n";
        }
    } else {
        echo "   ❌ Admins table does not exist\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ❌ Admin table check failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n3. Admin User Verification:\n";
try {
    $admin = Admin::where('email', 'admin@admin.com')->first();
    if ($admin) {
        echo "   ✅ Admin user found\n";
        echo "   📧 Email: " . $admin->email . "\n";
        echo "   👤 Name: " . $admin->f_name . " " . $admin->l_name . "\n";
        echo "   🆔 ID: " . $admin->id . "\n";
        
        // Test password
        $passwordCheck = Hash::check('12345678', $admin->password);
        echo "   🔑 Password verification: " . ($passwordCheck ? "✅ PASSED" : "❌ FAILED") . "\n";
        
        if (!$passwordCheck) {
            echo "   🔧 Updating password hash...\n";
            $admin->password = Hash::make('12345678');
            $admin->save();
            echo "   ✅ Password hash updated\n";
        }
    } else {
        echo "   ❌ Admin user not found\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ❌ Admin user check failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n4. Business Settings Check:\n";
try {
    $essentialSettings = [
        'system_language',
        'cash_on_delivery',
        'digital_payment',
        'admin_login_url'
    ];
    
    foreach ($essentialSettings as $setting) {
        $value = BusinessSetting::where('key', $setting)->first();
        if ($value) {
            echo "   ✅ $setting: configured\n";
        } else {
            echo "   ⚠️  $setting: missing\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Business settings check failed: " . $e->getMessage() . "\n";
}

echo "\n5. Login Routes Test:\n";
try {
    // Test login page route
    $response = file_get_contents('http://localhost:8000/login/admin');
    if ($response && strpos($response, 'Login') !== false) {
        echo "   ✅ Login page loads successfully\n";
        
        // Check for form action
        if (preg_match('/action="([^"]*)"/', $response, $matches)) {
            echo "   ✅ Login form action: " . $matches[1] . "\n";
        } else {
            echo "   ⚠️  Could not find form action\n";
        }
        
        // Check for CSRF token field
        if (strpos($response, '_token') !== false) {
            echo "   ✅ CSRF token field present\n";
        } else {
            echo "   ⚠️  CSRF token field missing\n";
        }
    } else {
        echo "   ❌ Login page failed to load\n";
    }
} catch (Exception $e) {
    echo "   ❌ Login route test failed: " . $e->getMessage() . "\n";
}

echo "\n6. Environment Check:\n";
try {
    echo "   🌍 APP_ENV: " . env('APP_ENV', 'not set') . "\n";
    echo "   🐛 APP_DEBUG: " . (env('APP_DEBUG', false) ? 'true' : 'false') . "\n";
    echo "   🔗 APP_URL: " . env('APP_URL', 'not set') . "\n";
    echo "   🗄️  DB_DATABASE: " . env('DB_DATABASE', 'not set') . "\n";
    echo "   🔑 APP_KEY: " . (env('APP_KEY') ? 'configured' : 'missing') . "\n";
} catch (Exception $e) {
    echo "   ❌ Environment check failed: " . $e->getMessage() . "\n";
}

echo "\n7. Session & Cache Check:\n";
try {
    // Clear caches
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    echo "   ✅ Caches cleared\n";
    
    // Check session driver
    echo "   📁 Session driver: " . config('session.driver', 'not configured') . "\n";
    echo "   🍪 Session lifetime: " . config('session.lifetime', 'not configured') . " minutes\n";
} catch (Exception $e) {
    echo "   ❌ Session/cache check failed: " . $e->getMessage() . "\n";
}

echo "\n================================\n";
echo "🎯 DIAGNOSIS COMPLETE\n";
echo "================================\n\n";

echo "📋 Login Information:\n";
echo "   URL: http://localhost:8000/login/admin\n";
echo "   Email: admin@admin.com\n";
echo "   Password: 12345678\n\n";

echo "🔧 If login still fails, check:\n";
echo "   1. Browser console for JavaScript errors\n";
echo "   2. Network tab for failed requests\n";
echo "   3. Laravel logs in storage/logs/laravel.log\n";
echo "   4. Server logs for HTTP errors\n\n";

echo "✨ Diagnostic complete!\n";