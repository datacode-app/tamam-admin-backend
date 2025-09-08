#!/bin/bash
# Permanent Database Monitor
echo "🔍 Database Health Monitor - $(date)"

# Kill any stale PHP processes
pkill -f "php artisan serve" 2>/dev/null || true

# Clear caches
php artisan cache:clear > /dev/null 2>&1
php artisan config:clear > /dev/null 2>&1

# Test database
php -r "
require_once \"vendor/autoload.php\";
\$app = require_once \"bootstrap/app.php\";
\$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
try {
    \$count = \Illuminate\Support\Facades\DB::table(\"orders\")->count();
    echo \"✅ Database healthy: \$count orders\n\";
} catch (Exception \$e) {
    echo \"❌ Database error: \" . \$e->getMessage() . \"\n\";
    exit(1);
}
"

# Start fresh server
nohup php artisan serve --host=0.0.0.0 --port=8000 > /tmp/laravel_server.log 2>&1 &
echo "🚀 Fresh Laravel server started"
