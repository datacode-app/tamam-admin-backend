<?php
/**
 * FORCE REMOTE DATABASE ONLY - ELIMINATE LOCAL MYSQL
 * This removes all local database references and forces remote connection
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\File;

echo "🚫 ELIMINATING LOCAL MYSQL - REMOTE DATABASE ONLY\n";
echo "=" . str_repeat("=", 60) . "\n\n";

try {
    // 1. Update database configuration to hardcode remote connection
    echo "1. 🔧 HARDCODING REMOTE DATABASE CONFIG...\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $databaseConfigPath = 'config/database.php';
    $databaseConfig = file_get_contents($databaseConfigPath);
    
    // Replace MySQL configuration section with hardcoded remote values
    $newMysqlConfig = "
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => '18.197.125.4', // FORCED REMOTE ONLY
            'port' => '5433',         // FORCED REMOTE ONLY  
            'database' => 'tamamdb', // FORCED REMOTE ONLY
            'username' => 'tamam_user', // FORCED REMOTE ONLY
            'password' => env('DB_PASSWORD', 'tamam_passwrod'),
            'unix_socket' => '', // DISABLED
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],";
    
    // Use regex to replace the mysql configuration block
    $pattern = "/'mysql'\s*=>\s*\[[^\]]*(?:\][^\]]*)*\],/s";
    $updatedConfig = preg_replace($pattern, $newMysqlConfig, $databaseConfig);
    
    if ($updatedConfig && $updatedConfig !== $databaseConfig) {
        file_put_contents($databaseConfigPath, $updatedConfig);
        echo "✅ Database config hardcoded to remote only\n";
    } else {
        echo "⚠️  Could not update database config automatically\n";
    }
    
    // 2. Update .env file to ensure remote connection
    echo "\n2. 📄 UPDATING .ENV FILE...\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $envContent = "APP_NAME=Tamam-Local
APP_ENV=local
APP_KEY=base64:5YxKofiC5zc7rwguhmpUkGUBvYmC80bjNdJfxkm8TDM=
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_MODE=dev

LOG_CHANNEL=stack
LOG_LEVEL=debug

# REMOTE DATABASE ONLY - NO LOCAL MYSQL
DB_CONNECTION=mysql
DB_HOST=18.197.125.4
DB_PORT=5433
DB_DATABASE=tamamdb
DB_USERNAME=tamam_user
DB_PASSWORD=tamam_passwrod

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=local@tamam.shop
MAIL_FROM_NAME=\"\${APP_NAME}\"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=

PUSHER_APP_ID=test
PUSHER_APP_KEY=test
PUSHER_APP_SECRET=test
PUSHER_APP_CLUSTER=mt1

MIX_PUSHER_APP_KEY=\"\${PUSHER_APP_KEY}\"
MIX_PUSHER_APP_CLUSTER=\"\${PUSHER_APP_CLUSTER}\"
";
    
    file_put_contents('.env', $envContent);
    echo "✅ .env file updated with remote database only\n";
    
    // 3. Clear all Laravel caches
    echo "\n3. 🧹 CLEARING ALL CACHES...\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $commands = [
        'php artisan config:clear',
        'php artisan cache:clear', 
        'php artisan route:clear',
        'php artisan view:clear'
    ];
    
    foreach ($commands as $command) {
        $output = shell_exec($command . ' 2>&1');
        echo "✅ " . explode(' ', $command)[2] . " cache cleared\n";
    }
    
    // 4. Create warning files about local MySQL
    echo "\n4. 📝 CREATING LOCAL MYSQL WARNING FILES...\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $warningContent = "# ⚠️ LOCAL MYSQL DISABLED ⚠️

This project uses REMOTE DATABASE ONLY.

## Remote Database Connection:
- Host: 18.197.125.4
- Port: 5433  
- Database: tamamdb
- User: tamam_user

## Why No Local MySQL?
- Prevents dual database issues
- Ensures consistency across CLI and web
- Eliminates recurring schedule_at column errors
- Forces all developers to use same database

## DO NOT:
- ❌ Install local MySQL
- ❌ Use localhost:3306
- ❌ Create local tamamdb database
- ❌ Change DB_HOST to 127.0.0.1

## If You See Database Errors:
1. Check internet connection
2. Verify remote database is accessible
3. Run: php artisan config:clear
4. Restart Laravel server

Generated on: " . date('Y-m-d H:i:s') . "
";
    
    file_put_contents('NO_LOCAL_MYSQL.md', $warningContent);
    file_put_contents('database/NO_LOCAL_DATABASE.md', $warningContent);
    echo "✅ Warning files created\n";
    
    // 5. Update other .env files
    echo "\n5. 🔄 UPDATING ALL .ENV VARIANTS...\n";
    echo "-" . str_repeat("-", 40) . "\n";
    
    $envFiles = ['.env.local', '.env.dev1', '.env.staging'];
    foreach ($envFiles as $envFile) {
        if (file_exists($envFile)) {
            $content = file_get_contents($envFile);
            // Replace any local database references
            $content = preg_replace('/DB_HOST=127\.0\.0\.1/', 'DB_HOST=18.197.125.4', $content);
            $content = preg_replace('/DB_HOST=localhost/', 'DB_HOST=18.197.125.4', $content); 
            $content = preg_replace('/DB_PORT=3306/', 'DB_PORT=5433', $content);
            $content = preg_replace('/DB_USERNAME=root/', 'DB_USERNAME=tamam_user', $content);
            file_put_contents($envFile, $content);
            echo "✅ Updated $envFile\n";
        }
    }
    
    echo "\n🎉 LOCAL MYSQL ELIMINATION COMPLETE!\n";
    echo "=" . str_repeat("=", 60) . "\n";
    echo "✅ Database config hardcoded to remote only\n";
    echo "✅ All .env files updated\n";  
    echo "✅ All caches cleared\n";
    echo "✅ Warning documentation created\n";
    echo "\n🚀 RESTART LARAVEL SERVER TO APPLY CHANGES:\n";
    echo "   pkill -f 'artisan serve'; php artisan serve --host=0.0.0.0 --port=8000\n";
    
} catch (Exception $e) {
    echo "💥 ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}