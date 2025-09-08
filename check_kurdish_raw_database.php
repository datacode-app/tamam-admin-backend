<?php
/**
 * Check Kurdish Raw Database - Direct MySQL Query
 * This will show us the exact raw data for Kurdish translations
 */

echo "🔍 KURDISH RAW DATABASE ANALYSIS\n";
echo "================================\n\n";

// Database connection details from production
$host = '18.197.125.4';
$port = '5433';  
$database = 'tamamdb';
$username = 'tamam_user';
$password = 'tamam_passwrod';

try {
    $pdo = new PDO(
        "mysql:host=$host:$port;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ Connected to production database successfully\n\n";
    
    // 1. Check if translations table exists and structure
    echo "1️⃣  **Translations Table Structure**\n";
    $stmt = $pdo->query("DESCRIBE translations");
    $columns = $stmt->fetchAll();
    
    echo "   Table structure:\n";
    foreach ($columns as $column) {
        echo "      • {$column['Field']} ({$column['Type']}) - {$column['Null']} - {$column['Key']}\n";
    }
    
    // 2. Count total translations
    echo "\n2️⃣  **Translation Statistics**\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM translations");
    $totalTranslations = $stmt->fetch()['total'];
    echo "   Total translations in database: $totalTranslations\n";
    
    // 3. Check all locales in database
    echo "\n3️⃣  **All Locales in Database**\n";
    $stmt = $pdo->query("SELECT locale, COUNT(*) as count FROM translations GROUP BY locale ORDER BY count DESC");
    $locales = $stmt->fetchAll();
    
    echo "   Found " . count($locales) . " different locales:\n";
    foreach ($locales as $locale) {
        echo "      • '{$locale['locale']}': {$locale['count']} translations\n";
    }
    
    // 4. Search for ANY Kurdish-related translations
    echo "\n4️⃣  **Kurdish Translation Search**\n";
    $kurdishPatterns = ['ckb', 'ku', 'kur', 'kurdish', 'sorani', 'central-kurdish', 'kmr'];
    
    foreach ($kurdishPatterns as $pattern) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM translations WHERE locale LIKE ?");
        $stmt->execute(["%$pattern%"]);
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            echo "   ✅ Found $count translations with locale pattern '$pattern'\n";
            
            // Show examples
            $stmt = $pdo->prepare("
                SELECT t.*, c.name as category_name 
                FROM translations t
                LEFT JOIN categories c ON c.id = t.translationable_id
                WHERE t.locale LIKE ? 
                AND t.translationable_type = 'App\\\\Models\\\\Category'
                LIMIT 3
            ");
            $stmt->execute(["%$pattern%"]);
            $examples = $stmt->fetchAll();
            
            foreach ($examples as $example) {
                echo "      → Category: \"{$example['category_name']}\" → \"{$example['value']}\" (locale: {$example['locale']})\n";
            }
        } else {
            echo "   ❌ No translations found with pattern '$pattern'\n";
        }
    }
    
    // 5. Check specifically for Category translations
    echo "\n5️⃣  **Category Translation Analysis**\n";
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            COUNT(DISTINCT translationable_id) as unique_categories,
            COUNT(DISTINCT locale) as unique_locales
        FROM translations 
        WHERE translationable_type = 'App\\\\Models\\\\Category'
    ");
    $catStats = $stmt->fetch();
    
    echo "   Category translations statistics:\n";
    echo "      • Total category translations: {$catStats['total']}\n";
    echo "      • Unique categories translated: {$catStats['unique_categories']}\n";
    echo "      • Languages available: {$catStats['unique_locales']}\n";
    
    // 6. Show sample categories and their translations
    echo "\n6️⃣  **Sample Categories with ALL Translations**\n";
    $stmt = $pdo->query("
        SELECT c.id, c.name, c.status, c.module_id
        FROM categories c
        WHERE c.status = 1 
        AND c.position = 0
        ORDER BY c.id
        LIMIT 5
    ");
    $sampleCategories = $stmt->fetchAll();
    
    foreach ($sampleCategories as $category) {
        echo "   📁 Category: \"{$category['name']}\" (ID: {$category['id']}, Module: {$category['module_id']})\n";
        
        // Get all translations for this category
        $stmt = $pdo->prepare("
            SELECT locale, `key`, value, created_at
            FROM translations 
            WHERE translationable_type = 'App\\\\Models\\\\Category' 
            AND translationable_id = ?
            ORDER BY locale, `key`
        ");
        $stmt->execute([$category['id']]);
        $translations = $stmt->fetchAll();
        
        if (count($translations) > 0) {
            echo "      Translations:\n";
            foreach ($translations as $trans) {
                echo "         • [{$trans['locale']}] {$trans['key']}: \"{$trans['value']}\" (added: {$trans['created_at']})\n";
            }
        } else {
            echo "      ❌ No translations found\n";
        }
        echo "\n";
    }
    
    // 7. Check for any Arabic script in translations (might be Kurdish)
    echo "7️⃣  **Arabic Script Search (Potential Kurdish)**\n";
    $stmt = $pdo->query("
        SELECT t.*, c.name as category_name
        FROM translations t
        LEFT JOIN categories c ON c.id = t.translationable_id
        WHERE t.translationable_type = 'App\\\\Models\\\\Category'
        AND (
            t.value REGEXP '[ئ-ی]' OR
            t.value REGEXP '[ا-ز]' OR
            t.value LIKE '%عراق%' OR
            t.value LIKE '%عێراق%'
        )
        LIMIT 10
    ");
    $arabicTranslations = $stmt->fetchAll();
    
    if (count($arabicTranslations) > 0) {
        echo "   ✅ Found " . count($arabicTranslations) . " translations with Arabic/Kurdish script:\n";
        foreach ($arabicTranslations as $trans) {
            echo "      • \"{$trans['category_name']}\" → \"{$trans['value']}\" (locale: {$trans['locale']})\n";
        }
    } else {
        echo "   ❌ No Arabic script translations found\n";
    }
    
    echo "\n8️⃣  **FINAL ANALYSIS**\n";
    echo "======================\n";
    
    if (count($arabicTranslations) > 0) {
        echo "   ✅ KURDISH TRANSLATIONS FOUND!\n";
        echo "   📝 Kurdish Sorani translations DO exist in the database\n";
        echo "   🔍 The issue might be in the locale matching or retrieval logic\n";
    } else {
        echo "   ❌ NO KURDISH TRANSLATIONS FOUND\n";
        echo "   📝 Kurdish Sorani translations need to be added to the database\n";
        echo "   🔧 Translation system is implemented but data is missing\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "🔧 Make sure database credentials are correct\n";
    echo "   Host: $host:$port\n";
    echo "   Database: $database\n";
    echo "   Username: $username\n";
}