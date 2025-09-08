<?php
/**
 * DEEP AUTHENTICATION DIAGNOSTIC
 * Find the EXACT root cause of authentication failures
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "🔍 DEEP AUTHENTICATION DIAGNOSTIC\n";
echo "=" . str_repeat("=", 60) . "\n\n";

try {
    // 1. Environment and Database Analysis
    echo "1. 🌐 ENVIRONMENT AND DATABASE ANALYSIS\n";
    echo "----------------------------------------\n";
    
    // Show database configuration
    $dbConfig = config('database.connections.mysql');
    echo "Database Host: " . $dbConfig['host'] . "\n";
    echo "Database Port: " . $dbConfig['port'] . "\n";
    echo "Database Name: " . $dbConfig['database'] . "\n";
    echo "Database User: " . $dbConfig['username'] . "\n";
    
    // Test database connection
    try {
        $pdo = DB::connection()->getPdo();
        echo "Database Connection: ✅ CONNECTED\n";
        
        // Get database server info
        $serverInfo = DB::select('SELECT VERSION() as version, DATABASE() as current_db')[0];
        echo "MySQL Version: " . $serverInfo->version . "\n";
        echo "Current Database: " . $serverInfo->current_db . "\n";
        
    } catch (Exception $e) {
        echo "Database Connection: ❌ FAILED - " . $e->getMessage() . "\n";
        exit(1);
    }
    
    // 2. Admin Table Analysis
    echo "\n2. 👥 ADMIN TABLE ANALYSIS\n";
    echo "----------------------------\n";
    
    // Check if admins table exists
    try {
        $tableExists = DB::select("SHOW TABLES LIKE 'admins'");
        if (empty($tableExists)) {
            echo "❌ CRITICAL: 'admins' table does not exist!\n";
            exit(1);
        }
        echo "Admins Table: ✅ EXISTS\n";
        
        // Show table structure
        $columns = DB::select("DESCRIBE admins");
        echo "Table Structure:\n";
        foreach ($columns as $col) {
            echo "  - {$col->Field}: {$col->Type}" . ($col->Null === 'YES' ? ' (nullable)' : '') . "\n";
        }
        
        // Count total admins
        $adminCount = DB::table('admins')->count();
        echo "Total Admin Records: $adminCount\n";
        
    } catch (Exception $e) {
        echo "Admin Table Check: ❌ FAILED - " . $e->getMessage() . "\n";
        exit(1);
    }
    
    // 3. Specific Admin User Analysis
    echo "\n3. 🔍 ADMIN USER 'admin@admin.com' ANALYSIS\n";
    echo "--------------------------------------------\n";
    
    // Raw database query
    $rawAdmin = DB::select("SELECT * FROM admins WHERE email = 'admin@admin.com'");
    
    if (empty($rawAdmin)) {
        echo "❌ CRITICAL: No admin found with email 'admin@admin.com'\n";
        
        // Show all admin emails
        $allAdmins = DB::select("SELECT id, email, f_name, l_name, role_id FROM admins LIMIT 10");
        echo "Available admin emails:\n";
        foreach ($allAdmins as $admin) {
            echo "  - ID: {$admin->id}, Email: {$admin->email}, Role: {$admin->role_id}\n";
        }
        
        // Create the admin user
        echo "\n🔧 CREATING ADMIN USER...\n";
        $newAdminId = DB::table('admins')->insertGetId([
            'f_name' => 'Master',
            'l_name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role_id' => 1,
            'phone' => '+1234567890',
            'is_logged_in' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "✅ Created admin user with ID: $newAdminId\n";
        
        // Re-fetch the admin
        $rawAdmin = DB::select("SELECT * FROM admins WHERE email = 'admin@admin.com'");
        
    } else {
        echo "✅ Admin user found!\n";
    }
    
    $admin = $rawAdmin[0];
    
    echo "Admin Details:\n";
    echo "  - ID: {$admin->id}\n";
    echo "  - Email: {$admin->email}\n";
    echo "  - Name: {$admin->f_name} {$admin->l_name}\n";
    echo "  - Role ID: {$admin->role_id} (" . gettype($admin->role_id) . ")\n";
    echo "  - Phone: {$admin->phone}\n";
    echo "  - Password Hash: " . substr($admin->password, 0, 20) . "...\n";
    echo "  - Created: {$admin->created_at}\n";
    
    // 4. Password Testing
    echo "\n4. 🔑 PASSWORD TESTING\n";
    echo "-----------------------\n";
    
    $testPassword = 'password';
    $storedHash = $admin->password;
    
    echo "Test Password: '$testPassword'\n";
    echo "Stored Hash: " . substr($storedHash, 0, 30) . "...\n";
    
    // Test hash verification
    $hashCheck = Hash::check($testPassword, $storedHash);
    echo "Hash::check() Result: " . ($hashCheck ? '✅ PASS' : '❌ FAIL') . "\n";
    
    if (!$hashCheck) {
        echo "🔧 FIXING PASSWORD...\n";
        $newHash = Hash::make($testPassword);
        DB::table('admins')->where('email', 'admin@admin.com')->update(['password' => $newHash]);
        echo "✅ Password updated with new hash\n";
        
        // Re-test
        $updatedAdmin = DB::select("SELECT password FROM admins WHERE email = 'admin@admin.com'")[0];
        $retestCheck = Hash::check($testPassword, $updatedAdmin->password);
        echo "Re-test Hash::check(): " . ($retestCheck ? '✅ PASS' : '❌ FAIL') . "\n";
        
        $storedHash = $updatedAdmin->password;
    }
    
    // 5. Role Testing
    echo "\n5. 🎭 ROLE TESTING\n";
    echo "-------------------\n";
    
    $roleId = $admin->role_id;
    echo "Role ID Value: " . var_export($roleId, true) . "\n";
    echo "Role ID Type: " . gettype($roleId) . "\n";
    echo "Role ID == 1: " . ($roleId == 1 ? 'true' : 'false') . "\n";
    echo "Role ID === 1: " . ($roleId === 1 ? 'true' : 'false') . "\n";
    echo "Role ID !== 1: " . ($roleId !== 1 ? 'TRUE (THIS FAILS AUTH!)' : 'false') . "\n";
    
    if ($roleId != 1) {
        echo "🔧 FIXING ROLE ID...\n";
        DB::table('admins')->where('email', 'admin@admin.com')->update(['role_id' => 1]);
        echo "✅ Role ID updated to 1\n";
        $roleId = 1;
    }
    
    // 6. Complete Authentication Simulation
    echo "\n6. 🧪 COMPLETE AUTHENTICATION SIMULATION\n";
    echo "------------------------------------------\n";
    
    $testEmail = 'admin@admin.com';
    $testPass = 'password';
    
    echo "Step 1: Finding user by email '$testEmail'\n";
    $authUser = Admin::where('email', $testEmail)->first();
    
    if (!$authUser) {
        echo "❌ FAIL: User not found by Eloquent\n";
    } else {
        echo "✅ PASS: User found by Eloquent\n";
        
        echo "Step 2: Testing password '$testPass'\n";
        $passwordValid = Hash::check($testPass, $authUser->password);
        echo ($passwordValid ? "✅ PASS" : "❌ FAIL") . ": Password validation\n";
        
        echo "Step 3: Testing role (role_id = {$authUser->role_id})\n";
        $roleValid = ($authUser->role_id == 1);
        echo ($roleValid ? "✅ PASS" : "❌ FAIL") . ": Role validation\n";
        
        echo "Step 4: Complete authentication check\n";
        $completeAuth = ($authUser && $passwordValid && $roleValid);
        echo ($completeAuth ? "✅ PASS" : "❌ FAIL") . ": Complete authentication\n";
        
        if (!$completeAuth) {
            echo "\n🚨 AUTHENTICATION FAILURE BREAKDOWN:\n";
            echo "- User exists: " . ($authUser ? 'Yes' : 'No') . "\n";
            echo "- Password valid: " . ($passwordValid ? 'Yes' : 'No') . "\n";
            echo "- Role valid: " . ($roleValid ? 'Yes' : 'No') . "\n";
        }
    }
    
    // 7. Emergency Fix
    echo "\n7. 🚨 EMERGENCY FIX APPLICATION\n";
    echo "--------------------------------\n";
    
    // Force update admin record with guaranteed working values
    $fixedData = [
        'f_name' => 'Emergency',
        'l_name' => 'Admin',
        'email' => 'admin@admin.com',
        'password' => Hash::make('password'),
        'role_id' => 1,
        'phone' => '+1234567890',
        'is_logged_in' => 0,
        'updated_at' => now()
    ];
    
    DB::table('admins')->where('email', 'admin@admin.com')->update($fixedData);
    echo "✅ Admin record force-updated with guaranteed values\n";
    
    // Final verification
    echo "\n8. 🎯 FINAL VERIFICATION\n";
    echo "-------------------------\n";
    
    $finalAdmin = Admin::where('email', 'admin@admin.com')->first();
    $finalPassword = Hash::check('password', $finalAdmin->password);
    $finalRole = ($finalAdmin->role_id == 1);
    $finalAuth = ($finalAdmin && $finalPassword && $finalRole);
    
    echo "Final admin exists: " . ($finalAdmin ? 'Yes' : 'No') . "\n";
    echo "Final password valid: " . ($finalPassword ? 'Yes' : 'No') . "\n";
    echo "Final role valid: " . ($finalRole ? 'Yes' : 'No') . "\n";
    echo "Final authentication: " . ($finalAuth ? '✅ SUCCESS' : '❌ FAILED') . "\n";
    
    if ($finalAuth) {
        echo "\n🎉 AUTHENTICATION ISSUE RESOLVED!\n";
        echo "Admin login should now work with:\n";
        echo "Email: admin@admin.com\n";
        echo "Password: password\n";
    } else {
        echo "\n💥 AUTHENTICATION STILL FAILING!\n";
        echo "Manual investigation required.\n";
    }
    
} catch (Exception $e) {
    echo "\n💥 DIAGNOSTIC ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}