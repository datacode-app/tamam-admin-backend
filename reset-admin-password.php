<?php
// Reset Admin Password Script
// Usage: php reset-admin-password.php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;

echo "=== Tamam Admin Password Reset ===\n\n";

// List all admin users
$admins = Admin::all(['id', 'email', 'f_name', 'l_name']);
echo "Available admin accounts:\n";
foreach ($admins as $admin) {
    echo "{$admin->id}. {$admin->email} ({$admin->f_name} {$admin->l_name})\n";
}

echo "\nEnter admin ID to reset password (or email): ";
$input = trim(fgets(STDIN));

// Find admin by ID or email
if (is_numeric($input)) {
    $admin = Admin::find($input);
} else {
    $admin = Admin::where('email', $input)->first();
}

if (!$admin) {
    echo "Admin not found!\n";
    exit(1);
}

echo "Resetting password for: {$admin->email}\n";
echo "Enter new password (minimum 8 characters): ";
$password = trim(fgets(STDIN));

if (strlen($password) < 8) {
    echo "Password must be at least 8 characters!\n";
    exit(1);
}

// Update password
$admin->password = bcrypt($password);
$admin->save();

echo "\n✅ Password successfully reset!\n";
echo "Email: {$admin->email}\n";
echo "New password: {$password}\n";
echo "\nYou can now login at: /login/admin\n";