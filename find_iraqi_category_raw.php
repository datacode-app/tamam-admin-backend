<?php
/**
 * Find Iraqi Category Complete Raw Data
 * Shows the entire Iraqi category data model with all translations
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Direct database connection
$pdo = new PDO(
    "mysql:host=" . $_ENV['DB_HOST'] . ":" . $_ENV['DB_PORT'] . ";dbname=" . $_ENV['DB_DATABASE'],
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "🔍 IRAQI CATEGORY COMPLETE RAW DATA ANALYSIS\n";
echo "==========================================\n\n";

try {
    echo "1️⃣  **Finding Iraqi Category**\n";
    
    // Search for Iraqi category with different patterns
    $searchPatterns = ['iraqi', 'Iraq', 'عراق', 'عێراق', '%iraqi%', '%عراق%'];
    
    $foundCategories = [];
    
    foreach ($searchPatterns as $pattern) {
        $stmt = $pdo->prepare("
            SELECT 
                id, name, image, parent_id, position, status, 
                featured, module_id, priority, created_at, updated_at,
                products_count, childes_count, slug
            FROM categories 
            WHERE name LIKE :pattern 
            OR slug LIKE :pattern
            ORDER BY id
        ");
        $stmt->execute([':pattern' => $pattern]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($results) > 0) {
            echo "   🎯 Found with pattern '$pattern':\n";
            foreach ($results as $category) {
                $foundCategories[$category['id']] = $category;
                echo "      • ID: {$category['id']} | Name: \"{$category['name']}\" | Status: {$category['status']} | Module: {$category['module_id']}\n";
            }
        }
    }
    
    if (empty($foundCategories)) {
        echo "   ❌ No Iraqi category found with common patterns\n";
        echo "   🔍 Let's check all food categories (module_id = 1)...\n\n";
        
        $stmt = $pdo->prepare("
            SELECT id, name, slug, status, module_id, position
            FROM categories 
            WHERE module_id = 1 AND status = 1 AND position = 0
            ORDER BY name
        ");
        $stmt->execute();
        $allFoodCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "   📋 All Food Categories:\n";
        foreach ($allFoodCategories as $category) {
            echo "      • ID: {$category['id']} | Name: \"{$category['name']}\" | Slug: \"{$category['slug']}\"\n";
        }
        
        // Let's pick the first one for analysis
        if (count($allFoodCategories) > 0) {
            $foundCategories[$allFoodCategories[0]['id']] = $allFoodCategories[0];
            echo "\n   🎯 Using first category for analysis: \"{$allFoodCategories[0]['name']}\"\n";
        }
    }
    
    echo "\n";
    
    // Now analyze each found category
    foreach ($foundCategories as $categoryId => $category) {
        echo "2️⃣  **COMPLETE RAW DATA FOR CATEGORY ID: {$categoryId}**\n";
        echo "======================================================\n";
        
        echo "📊 **Categories Table Data:**\n";
        foreach ($category as $field => $value) {
            $displayValue = $value !== null ? $value : 'NULL';
            echo "   {$field}: {$displayValue}\n";
        }
        
        echo "\n🌐 **ALL TRANSLATIONS FOR THIS CATEGORY:**\n";
        
        $stmt = $pdo->prepare("
            SELECT 
                id, translationable_type, translationable_id, 
                locale, `key`, value, created_at, updated_at
            FROM translations 
            WHERE translationable_type = 'App\\\\Models\\\\Category' 
            AND translationable_id = :category_id
            ORDER BY locale, `key`
        ");
        $stmt->execute([':category_id' => $categoryId]);
        $translations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($translations) > 0) {
            echo "   ✅ Found " . count($translations) . " translations:\n";
            foreach ($translations as $translation) {
                echo "      • ID: {$translation['id']}\n";
                echo "        Locale: \"{$translation['locale']}\"\n";
                echo "        Key: \"{$translation['key']}\"\n";
                echo "        Value: \"{$translation['value']}\"\n";
                echo "        Created: {$translation['created_at']}\n";
                echo "        Updated: {$translation['updated_at']}\n";
                echo "        ─────────────────────────────\n";
            }
        } else {
            echo "   ❌ No translations found for this category\n";
        }
        
        echo "\n🔍 **KURDISH-SPECIFIC TRANSLATIONS:**\n";
        
        $kurdishLocales = ['ku', 'ckb', 'kmr', 'kurdish', 'sorani', 'central-kurdish'];
        $foundKurdish = false;
        
        foreach ($kurdishLocales as $locale) {
            $stmt = $pdo->prepare("
                SELECT id, locale, `key`, value, created_at, updated_at
                FROM translations 
                WHERE translationable_type = 'App\\\\Models\\\\Category' 
                AND translationable_id = :category_id
                AND locale = :locale
            ");
            $stmt->execute([':category_id' => $categoryId, ':locale' => $locale]);
            $kurdishTrans = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($kurdishTrans) > 0) {
                $foundKurdish = true;
                echo "   🎯 Found Kurdish translation with locale '$locale':\n";
                foreach ($kurdishTrans as $trans) {
                    echo "      • Key: \"{$trans['key']}\" → Value: \"{$trans['value']}\"\n";
                    echo "      • Created: {$trans['created_at']}\n";
                    echo "      • Updated: {$trans['updated_at']}\n";
                }
            }
        }
        
        if (!$foundKurdish) {
            echo "   ❌ No Kurdish translations found for any Kurdish locale variants\n";
        }
        
        echo "\n📱 **STORAGE/IMAGE DATA:**\n";
        
        $stmt = $pdo->prepare("
            SELECT id, data_type, data_id, `key`, value, created_at, updated_at
            FROM storages 
            WHERE data_type = 'App\\\\Models\\\\Category' 
            AND data_id = :category_id
        ");
        $stmt->execute([':category_id' => $categoryId]);
        $storages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($storages) > 0) {
            echo "   📁 Found " . count($storages) . " storage records:\n";
            foreach ($storages as $storage) {
                echo "      • Key: \"{$storage['key']}\" → Value: \"{$storage['value']}\"\n";
                echo "      • Created: {$storage['created_at']}\n";
            }
        } else {
            echo "   ❌ No storage records found\n";
        }
        
        echo "\n" . str_repeat("=", 60) . "\n\n";
    }
    
    echo "3️⃣  **SEARCH FOR KURDISH TRANSLATIONS IN ENTIRE DATABASE**\n";
    echo "=========================================================\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            t.id, t.translationable_id, t.locale, t.key, t.value,
            c.name as category_name, c.module_id
        FROM translations t
        LEFT JOIN categories c ON c.id = t.translationable_id
        WHERE t.translationable_type = 'App\\\\Models\\\\Category'
        AND (
            t.locale LIKE '%ku%' OR 
            t.locale LIKE '%ckb%' OR 
            t.locale LIKE '%sorani%' OR 
            t.locale LIKE '%kurdish%' OR
            t.value LIKE '%عێراقی%' OR
            t.value LIKE '%عراقی%' OR
            t.value LIKE '%ئیتالی%'
        )
        ORDER BY t.translationable_id, t.locale
    ");
    $stmt->execute();
    $allKurdishTranslations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($allKurdishTranslations) > 0) {
        echo "   🎯 Found " . count($allKurdishTranslations) . " Kurdish-related translations:\n\n";
        foreach ($allKurdishTranslations as $trans) {
            echo "      📝 Translation ID: {$trans['id']}\n";
            echo "         Category: \"{$trans['category_name']}\" (ID: {$trans['translationable_id']}, Module: {$trans['module_id']})\n";
            echo "         Locale: \"{$trans['locale']}\"\n";
            echo "         Key: \"{$trans['key']}\"\n";
            echo "         Value: \"{$trans['value']}\"\n";
            echo "         " . str_repeat("─", 50) . "\n";
        }
    } else {
        echo "   ❌ No Kurdish translations found in entire database\n";
    }
    
    echo "\n4️⃣  **SUMMARY**\n";
    echo "==============\n";
    
    if (count($foundCategories) > 0) {
        echo "   📊 Analyzed " . count($foundCategories) . " categories\n";
        echo "   🌐 Total translations found: " . count($allKurdishTranslations) . "\n";
        
        if (count($allKurdishTranslations) > 0) {
            echo "   ✅ Kurdish translations DO exist in the database!\n";
            echo "   🔍 Check the specific locales and values above\n";
        } else {
            echo "   ❌ No Kurdish translations found\n";
            echo "   💡 Need to add Kurdish translations for food categories\n";
        }
    } else {
        echo "   ❌ No Iraqi category found in database\n";
        echo "   🔍 Check if it exists with a different name\n";
    }

} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    echo "🔧 Make sure the development environment is running:\n";
    echo "   ./start-simple-dev.sh\n";
}