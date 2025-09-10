<?php
/**
 * Test script to verify upload logging is working
 * Use this after attempting an upload via admin panel
 * Run: php verify_upload_logging.php
 */

echo "=== UPLOAD LOGGING VERIFICATION ===\n";

$logFile = "/Users/hooshyar/Desktop/development/tamam-workspace/Admin-with-Rental/storage/logs/laravel.log";

if (!file_exists($logFile)) {
    echo "❌ Log file not found: $logFile\n";
    exit;
}

echo "\n📋 Recent upload-related log entries:\n";

// Get recent log entries related to uploads
$logLines = file($logFile);
$recentLines = array_slice($logLines, -100); // Last 100 lines

$uploadLogs = [];
foreach ($recentLines as $line) {
    if (strpos($line, 'Upload') !== false || 
        strpos($line, 'upload') !== false || 
        strpos($line, 'Store logo') !== false ||
        strpos($line, 'Store cover') !== false) {
        $uploadLogs[] = trim($line);
    }
}

if (empty($uploadLogs)) {
    echo "   ⚠️ No recent upload logs found\n";
    echo "   Try uploading an image via admin panel first\n";
} else {
    echo "   Found " . count($uploadLogs) . " upload-related log entries:\n";
    foreach (array_slice($uploadLogs, -10) as $log) {
        echo "   " . $log . "\n";
    }
}

echo "\n=== VERIFICATION COMPLETE ===\n";
