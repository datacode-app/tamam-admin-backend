<?php
// Dashboard bypass
require_once "../vendor/autoload.php";
$app = require_once "../bootstrap/app.php";
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Auth;

// Check if admin is logged in
if (!Auth::guard("admin")->check()) {
    header("Location: /emergency-login.php");
    exit;
}

$admin = Auth::guard("admin")->user();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Emergency</title>
    <style>
        body { font-family: Arial; margin: 0; background: #f8f9fa; }
        .header { background: #28a745; color: white; padding: 20px; text-align: center; }
        .container { max-width: 1200px; margin: 40px auto; padding: 20px; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 20px 0; }
        .success { color: #28a745; font-weight: bold; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Admin Dashboard - LOGIN SUCCESSFUL!</h1>
        <p>Welcome <?php echo htmlspecialchars($admin->f_name . " " . $admin->l_name); ?></p>
    </div>
    
    <div class="container">
        <div class="card">
            <h2 class="success">✅ Emergency Login System Working!</h2>
            <p>You have successfully logged in using the emergency bypass system.</p>
            
            <div class="info">
                <strong>🛠️ System Status:</strong><br>
                • Emergency login: ✅ Working<br>
                • User authenticated: ✅ <?php echo $admin->email; ?><br>
                • Role ID: ✅ <?php echo $admin->role_id; ?> (Admin)<br>
                • Session: ✅ Active<br>
            </div>
            
            <h3>Quick Actions:</h3>
            <a href="/admin/dashboard" class="btn">Try Original Dashboard</a>
            <a href="/admin/business-settings/business-setup" class="btn">Business Settings</a>
            <a href="/logout" class="btn btn-danger">Logout</a>
            
            <h3>Next Steps:</h3>
            <p>Now that login is working, you can:</p>
            <ul>
                <li>Access admin functions through the original Laravel routes</li>
                <li>Use this emergency system as backup if main login fails again</li>
                <li>The routing issues have been bypassed successfully</li>
            </ul>
        </div>
    </div>
</body>
</html>