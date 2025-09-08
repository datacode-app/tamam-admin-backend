<?php

echo "🚨 BULK IMPORT UPLOAD FAILURE DIAGNOSTIC\n";
echo "=========================================\n";
echo "Diagnosing common causes of 'failed to upload' errors\n\n";

// Common file paths for testing
$testFiles = [
    '/Users/hooshyar/Downloads/stores_multilang_template (6) 38.xlsx',
    'test-files-multilingual-stores.csv',
    'sample-bulk-import-testing.csv'
];

foreach ($testFiles as $filePath) {
    echo "📁 TESTING FILE: " . basename($filePath) . "\n";
    echo str_repeat("-", 50) . "\n";
    
    if (!file_exists($filePath)) {
        echo "❌ File does not exist at: {$filePath}\n";
        echo "   Check if file path is correct\n";
        continue;
    }
    
    // Check file properties
    $fileSize = filesize($filePath);
    $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
    $mimeType = mime_content_type($filePath);
    
    echo "📊 File Information:\n";
    echo "   Size: " . number_format($fileSize / 1024, 2) . " KB\n";
    echo "   Extension: {$fileExtension}\n";
    echo "   MIME Type: {$mimeType}\n";
    
    // Check Laravel validation rules
    echo "\n🔍 Laravel Validation Checks:\n";
    
    // File size check (max 20MB = 20480 KB)
    $maxSizeKB = 20480;
    if ($fileSize / 1024 > $maxSizeKB) {
        echo "   ❌ File too large: " . number_format($fileSize / 1024, 2) . "KB > {$maxSizeKB}KB\n";
    } else {
        echo "   ✅ File size OK: " . number_format($fileSize / 1024, 2) . "KB\n";
    }
    
    // MIME type check
    $allowedMimes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                     'application/vnd.ms-excel', 
                     'text/csv'];
    $allowedExtensions = ['xlsx', 'xls', 'csv'];
    
    if (in_array(strtolower($fileExtension), $allowedExtensions)) {
        echo "   ✅ Extension allowed: {$fileExtension}\n";
    } else {
        echo "   ❌ Extension not allowed: {$fileExtension}\n";
    }
    
    if (in_array($mimeType, $allowedMimes)) {
        echo "   ✅ MIME type allowed: {$mimeType}\n";
    } else {
        echo "   ⚠️  MIME type may cause issues: {$mimeType}\n";
    }
    
    // Try to read file with FastExcel equivalent (simulated)
    echo "\n📖 File Content Analysis:\n";
    
    if (strtolower($fileExtension) === 'csv') {
        // For CSV files, check encoding and structure
        $handle = fopen($filePath, 'r');
        if ($handle) {
            $firstLine = fgets($handle);
            fclose($handle);
            
            echo "   First line: " . trim(substr($firstLine, 0, 100)) . "...\n";
            
            // Check for common encoding issues
            if (!mb_check_encoding($firstLine, 'UTF-8')) {
                echo "   ⚠️  Encoding issue detected - not UTF-8\n";
            } else {
                echo "   ✅ UTF-8 encoding OK\n";
            }
            
            // Count columns
            $columns = str_getcsv($firstLine);
            echo "   Column count: " . count($columns) . "\n";
            echo "   Sample columns: " . implode(', ', array_slice($columns, 0, 5)) . "\n";
        }
    } else {
        // For Excel files, basic checks
        echo "   Excel file - checking for corruption indicators\n";
        
        // Try to read first few bytes
        $handle = fopen($filePath, 'rb');
        $header = fread($handle, 8);
        fclose($handle);
        
        // Check for Excel file signatures
        if (strpos($header, 'PK') === 0) {
            echo "   ✅ Excel file signature OK (ZIP-based format)\n";
        } else {
            echo "   ❌ Invalid Excel file signature\n";
        }
    }
    
    echo "\n";
}

echo "🔧 COMMON UPLOAD FAILURE CAUSES:\n";
echo "=================================\n";
echo "1. ❌ File size > 20MB limit\n";
echo "2. ❌ Wrong file extension (not .xlsx, .xls, .csv)\n";
echo "3. ❌ Corrupted Excel file\n";
echo "4. ❌ Encoding issues in CSV (not UTF-8)\n";
echo "5. ❌ Missing required columns\n";
echo "6. ❌ Duplicate emails/phones in file\n";
echo "7. ❌ Email/phone already exists in database\n";
echo "8. ❌ Invalid zone_id or module_id values\n";
echo "9. ❌ Empty required fields\n";
echo "10. ❌ Invalid delivery time format\n";

echo "\n🛠️  TROUBLESHOOTING STEPS FOR CLIENT:\n";
echo "=====================================\n";
echo "1. ✅ Check file size is under 20MB\n";
echo "2. ✅ Save Excel file as .xlsx format (not .xls)\n";
echo "3. ✅ Ensure all required columns are filled:\n";
echo "   - ownerFirstName, storeName, phone, email\n";
echo "   - latitude, longitude, zone_id, DeliveryTime\n";
echo "   - Tax, logo\n";
echo "4. ✅ Check for duplicate emails/phones within file\n";
echo "5. ✅ Use unique emails/phones not in database\n";
echo "6. ✅ Use valid zone_id (1, 2, 3, etc.)\n";
echo "7. ✅ Use valid module_id (1=Food, 2=Grocery, etc.)\n";
echo "8. ✅ Format delivery time as '20-100 min'\n";
echo "9. ✅ Check Laravel logs for detailed error messages\n";

echo "\n📝 ENHANCED ERROR REPORTING NOW ACTIVE:\n";
echo "=======================================\n";
echo "✅ File validation errors now show specific details\n";
echo "✅ Missing field errors show exact row and column\n";
echo "✅ Duplicate detection shows conflicting values\n";
echo "✅ Database conflicts show existing emails/phones\n";
echo "✅ All errors are logged for technical debugging\n";

echo "\n✅ DIAGNOSTIC COMPLETE - UPLOAD ERRORS SHOULD NOW BE CLEAR!\n";

?>