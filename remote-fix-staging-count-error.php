<?php

echo "🔧 REMOTE FIX FOR STAGING COUNT() ERROR\n";
echo "=======================================\n";
echo "This script provides direct commands to fix staging.tamam.shop\n\n";

echo "🎯 PROBLEM SUMMARY:\n";
echo "==================\n";
echo "• Error: count(): Argument #1 must be of type Countable|array, null given\n";
echo "• File: /var/www/tamam/resources/views/layouts/admin/partials/_sidebar_settings.blade.php\n";
echo "• Cause: config('addon_admin_routes') returning null\n";
echo "• Impact: Admin panel completely inaccessible\n\n";

echo "🚀 IMMEDIATE FIX COMMANDS:\n";
echo "=========================\n";
echo "Run these commands on staging.tamam.shop server:\n\n";

echo "# 1. Navigate to Laravel root\n";
echo "cd /var/www/tamam\n\n";

echo "# 2. Backup original file\n";
echo "cp resources/views/layouts/admin/partials/_sidebar_settings.blade.php resources/views/layouts/admin/partials/_sidebar_settings.blade.php.backup\n\n";

echo "# 3. Apply the fix using sed (inline replacement)\n";
echo "sed -i 's|count(config(\"addon_admin_routes\"))|count(config(\"addon_admin_routes\") ?? [])|g' resources/views/layouts/admin/partials/_sidebar_settings.blade.php\n\n";

echo "# 4. Clear all Laravel caches (CRITICAL!)\n";
echo "php artisan cache:clear\n";
echo "php artisan config:clear\n";
echo "php artisan view:clear\n";
echo "php artisan route:clear\n\n";

echo "# 5. Optional: Restart PHP-FPM\n";
echo "sudo service php8.2-fpm restart\n";
echo "# OR\n";
echo "sudo service php-fpm restart\n\n";

echo "# 6. Verify the fix\n";
echo "grep -n \"addon_admin_routes\" resources/views/layouts/admin/partials/_sidebar_settings.blade.php\n\n";

echo "✅ VERIFICATION COMMANDS:\n";
echo "========================\n";
echo "# Test admin panel (should work now)\n";
echo "curl -I https://staging.tamam.shop/admin\n\n";
echo "# Test API (should return JSON)\n";
echo "curl -s https://staging.tamam.shop/api/v1/config | head -n 5\n\n";

echo "📋 ALTERNATIVE MANUAL FIX:\n";
echo "==========================\n";
echo "If sed command doesn't work, manually edit the file:\n\n";

echo "vim /var/www/tamam/resources/views/layouts/admin/partials/_sidebar_settings.blade.php\n\n";

echo "Find these lines (around line 492 and 506):\n";
echo "  OLD: @if(count(config('addon_admin_routes'))>0)\n";
echo "  NEW: @if(count(config('addon_admin_routes') ?? [])>0)\n\n";

echo "  OLD: @foreach(config('addon_admin_routes') as \$routes)\n";
echo "  NEW: @foreach(config('addon_admin_routes') ?? [] as \$routes)\n\n";

echo "Save and exit, then run cache clear commands.\n\n";

echo "🔍 WHAT THE FIX DOES:\n";
echo "====================\n";
echo "• Adds null coalescing operator (??) to handle null config values\n";
echo "• When config('addon_admin_routes') returns null, use empty array [] instead\n";
echo "• count([]) returns 0 safely, preventing the error\n";
echo "• foreach([], ...) handles empty arrays safely\n\n";

echo "⚠️  CRITICAL NOTES:\n";
echo "==================\n";
echo "• Cache clearing is MANDATORY - the fix won't work without it\n";
echo "• The server may need PHP-FPM restart for compiled views\n";
echo "• Test immediately after applying the fix\n";
echo "• The error occurs because Modules/Gateways directory doesn't exist\n\n";

echo "📞 SUCCESS INDICATORS:\n";
echo "=====================\n";
echo "• https://staging.tamam.shop/admin loads without 500 error\n";
echo "• Admin login form appears\n";
echo "• No 'count(): Argument #1' errors in logs\n";
echo "• API endpoints continue working normally\n\n";

echo "✅ ONE-LINER QUICK FIX:\n";
echo "=======================\n";
echo "cd /var/www/tamam && sed -i 's|count(config(\"addon_admin_routes\"))|count(config(\"addon_admin_routes\") ?? [])|g' resources/views/layouts/admin/partials/_sidebar_settings.blade.php && php artisan view:clear && php artisan cache:clear\n\n";

echo "🎉 AFTER RUNNING THE FIX:\n";
echo "=========================\n";
echo "The admin panel should be accessible and functional!\n";
echo "Test URL: https://staging.tamam.shop/admin\n\n";

?>