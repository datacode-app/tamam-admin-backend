<?php
// Intercept login attempts and debug in real-time
require_once "../vendor/autoload.php";
$app = require_once "../bootstrap/app.php";
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

header('Content-Type: text/plain');

echo "🔍 LOGIN INTERCEPT DEBUG\n";
echo "========================\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

if ($_POST) {
    echo "📧 POST Data Received:\n";
    echo "-----------------------\n";
    foreach ($_POST as $key => $value) {
        if ($key === 'password') {
            echo "$key: [" . strlen($value) . " characters]\n";
        } elseif ($key === '_token') {
            echo "$key: " . substr($value, 0, 20) . "...\n";
        } else {
            echo "$key: $value\n";
        }
    }
    echo "\n";
    
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "";
    
    echo "🔍 AUTHENTICATION PROCESS:\n";
    echo "==========================\n";
    
    // Step 1: User lookup
    echo "Step 1: Looking up user '$email'...\n";
    try {
        $admin = Admin::where("email", $email)->first();
        
        if ($admin) {
            echo "✅ User found!\n";
            echo "   - ID: {$admin->id}\n";
            echo "   - Email: {$admin->email}\n";
            echo "   - Role ID: {$admin->role_id} (" . gettype($admin->role_id) . ")\n";
            echo "   - Name: {$admin->f_name} {$admin->l_name}\n";
            
            // Step 2: Password check
            echo "\nStep 2: Testing password...\n";
            $passwordValid = Hash::check($password, $admin->password);
            echo "Password valid: " . ($passwordValid ? "✅ YES" : "❌ NO") . "\n";
            
            if (!$passwordValid) {
                echo "   Hash in DB: " . substr($admin->password, 0, 30) . "...\n";
                echo "   Test password: '$password'\n";
            }
            
            // Step 3: Role check
            echo "\nStep 3: Testing role...\n";
            echo "Required role: '$role'\n";
            echo "User role_id: " . var_export($admin->role_id, true) . "\n";
            
            if ($role === 'admin_employee' && $admin->role_id === 1) {
                echo "❌ FAIL: admin_employee cannot have role_id 1\n";
            } elseif ($role === 'admin' && $admin->role_id !== 1) {
                echo "❌ FAIL: admin must have role_id 1\n";
                echo "   Expected: 1\n";
                echo "   Got: " . var_export($admin->role_id, true) . "\n";
            } else {
                echo "✅ PASS: Role validation successful\n";
            }
            
            // Step 4: Final result
            echo "\nStep 4: Final authentication result...\n";
            $finalResult = ($admin && $passwordValid && (
                ($role === 'admin' && $admin->role_id == 1) ||
                ($role === 'admin_employee' && $admin->role_id != 1)
            ));
            
            echo "Final result: " . ($finalResult ? "✅ SUCCESS" : "❌ FAILURE") . "\n";
            
            if (!$finalResult) {
                echo "\nFailure breakdown:\n";
                echo "- User exists: " . ($admin ? "Yes" : "No") . "\n";
                echo "- Password valid: " . ($passwordValid ? "Yes" : "No") . "\n";
                echo "- Role valid: " . ((($role === 'admin' && $admin->role_id == 1) || ($role === 'admin_employee' && $admin->role_id != 1)) ? "Yes" : "No") . "\n";
            }
            
        } else {
            echo "❌ No user found with email '$email'\n";
            
            echo "\nAvailable admin users:\n";
            $allAdmins = Admin::select('id', 'email', 'role_id')->limit(5)->get();
            foreach ($allAdmins as $user) {
                echo "   - ID: {$user->id}, Email: {$user->email}, Role: {$user->role_id}\n";
            }
        }
        
    } catch (Exception $e) {
        echo "💥 Database error: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    
} else {
    echo "❌ No POST data received\n";
    echo "This script should receive POST data from a login form.\n\n";
    echo "To test, submit the login form with action pointing to this script.\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Debug completed at " . date('Y-m-d H:i:s') . "\n";
?>