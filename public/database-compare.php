<?php
// Compare direct database vs Eloquent to find discrepancy
header('Content-Type: text/plain');

require_once "../vendor/autoload.php";
$app = require_once "../bootstrap/app.php";
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "🔍 DATABASE VS ELOQUENT COMPARISON\n";
echo "===================================\n\n";

$email = 'admin@admin.com';

try {
    echo "1. DIRECT DATABASE QUERY:\n";
    echo "-------------------------\n";
    $rawAdmin = DB::select("SELECT id, email, f_name, l_name, role_id, password, created_at FROM admins WHERE email = ?", [$email]);
    
    if (!empty($rawAdmin)) {
        $raw = $rawAdmin[0];
        echo "✅ Found user via DB::select\n";
        echo "  - ID: {$raw->id}\n";
        echo "  - Email: {$raw->email}\n";
        echo "  - Name: {$raw->f_name} {$raw->l_name}\n";
        echo "  - Role ID: " . var_export($raw->role_id, true) . " (" . gettype($raw->role_id) . ")\n";
        echo "  - Password: " . substr($raw->password, 0, 20) . "...\n";
        echo "  - Created: {$raw->created_at}\n";
        
        // Test password with raw data
        $rawPasswordTest = Hash::check('password', $raw->password);
        echo "  - Password test: " . ($rawPasswordTest ? "✅ PASS" : "❌ FAIL") . "\n";
    } else {
        echo "❌ No user found via DB::select\n";
    }
    
    echo "\n2. ELOQUENT MODEL QUERY:\n";
    echo "------------------------\n";
    $eloquentAdmin = Admin::where('email', $email)->first();
    
    if ($eloquentAdmin) {
        echo "✅ Found user via Eloquent\n";
        echo "  - ID: {$eloquentAdmin->id}\n";
        echo "  - Email: {$eloquentAdmin->email}\n";
        echo "  - Name: {$eloquentAdmin->f_name} {$eloquentAdmin->l_name}\n";
        echo "  - Role ID: " . var_export($eloquentAdmin->role_id, true) . " (" . gettype($eloquentAdmin->role_id) . ")\n";
        echo "  - Password: " . substr($eloquentAdmin->password ?? 'NULL', 0, 20) . "...\n";
        echo "  - Created: {$eloquentAdmin->created_at}\n";
        
        // Test password with eloquent data
        if ($eloquentAdmin->password) {
            $eloquentPasswordTest = Hash::check('password', $eloquentAdmin->password);
            echo "  - Password test: " . ($eloquentPasswordTest ? "✅ PASS" : "❌ FAIL") . "\n";
        } else {
            echo "  - Password: NULL - cannot test\n";
        }
    } else {
        echo "❌ No user found via Eloquent\n";
    }
    
    echo "\n3. COMPARISON ANALYSIS:\n";
    echo "-----------------------\n";
    if (!empty($rawAdmin) && $eloquentAdmin) {
        $raw = $rawAdmin[0];
        
        echo "ID matches: " . ($raw->id == $eloquentAdmin->id ? "✅ YES" : "❌ NO") . "\n";
        echo "Email matches: " . ($raw->email == $eloquentAdmin->email ? "✅ YES" : "❌ NO") . "\n";
        echo "Role ID matches: " . ($raw->role_id == $eloquentAdmin->role_id ? "✅ YES" : "❌ NO") . "\n";
        echo "Password matches: " . ($raw->password == $eloquentAdmin->password ? "✅ YES" : "❌ NO") . "\n";
        
        if ($raw->role_id != $eloquentAdmin->role_id) {
            echo "\n🚨 ROLE ID MISMATCH DETECTED!\n";
            echo "Database role_id: " . var_export($raw->role_id, true) . "\n";
            echo "Eloquent role_id: " . var_export($eloquentAdmin->role_id, true) . "\n";
        }
        
        if ($raw->password != $eloquentAdmin->password) {
            echo "\n🚨 PASSWORD MISMATCH DETECTED!\n";
            echo "Database password: " . substr($raw->password, 0, 30) . "...\n";
            echo "Eloquent password: " . substr($eloquentAdmin->password ?? 'NULL', 0, 30) . "...\n";
        }
        
    }
    
    echo "\n4. DATABASE CONNECTION INFO:\n";
    echo "----------------------------\n";
    $config = config('database.connections.mysql');
    echo "Host: {$config['host']}\n";
    echo "Port: {$config['port']}\n";
    echo "Database: {$config['database']}\n";
    echo "User: {$config['username']}\n";
    
    $currentDb = DB::select('SELECT DATABASE() as db')[0];
    echo "Current DB: {$currentDb->db}\n";
    
    echo "\n5. EMERGENCY FIX:\n";
    echo "-----------------\n";
    if (!empty($rawAdmin)) {
        $raw = $rawAdmin[0];
        
        // Fix the user directly in database
        echo "Fixing user directly in database...\n";
        $updated = DB::update("UPDATE admins SET role_id = 1, password = ? WHERE email = ?", [
            Hash::make('password'),
            $email
        ]);
        echo "Updated rows: $updated\n";
        
        // Clear any Eloquent cache
        if (method_exists(Admin::class, 'flushEventListeners')) {
            Admin::flushEventListeners();
        }
        
        // Re-test with fresh data
        echo "\nRe-testing after fix...\n";
        $newAdmin = Admin::where('email', $email)->first();
        if ($newAdmin) {
            $newPasswordTest = Hash::check('password', $newAdmin->password);
            echo "New role_id: " . var_export($newAdmin->role_id, true) . "\n";
            echo "New password test: " . ($newPasswordTest ? "✅ PASS" : "❌ FAIL") . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "💥 ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Comparison completed at " . date('Y-m-d H:i:s') . "\n";
?>