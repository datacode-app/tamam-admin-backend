<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\BusinessSetting;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 Adding Kurdish 'ku' language code for backward compatibility..." . PHP_EOL;

try {
    // Add 'ku' to the language list if not already present
    $languageList = BusinessSetting::where('key', 'language')->first();
    if ($languageList) {
        $languages = json_decode($languageList->value, true);
        if (!in_array('ku', $languages)) {
            $languages[] = 'ku';
            $languageList->update(['value' => json_encode($languages)]);
            echo "✅ Added 'ku' to language list" . PHP_EOL;
        } else {
            echo "✅ 'ku' already exists in language list" . PHP_EOL;
        }
        echo "📋 Current languages: " . $languageList->value . PHP_EOL;
    }

    // Add 'ku' to system_language with proper configuration
    $systemLanguage = BusinessSetting::where('key', 'system_language')->first();
    if ($systemLanguage) {
        $languages = json_decode($systemLanguage->value, true);
        
        // Check if 'ku' already exists
        $kuExists = false;
        foreach ($languages as $language) {
            if (isset($language['code']) && $language['code'] === 'ku') {
                $kuExists = true;
                break;
            }
        }
        
        if (!$kuExists) {
            // Add 'ku' language configuration
            $languages[] = [
                'id' => 'ku_compat',
                'direction' => 'rtl',
                'code' => 'ku',
                'status' => 1,
                'default' => false,
                'name' => 'Kurdish (Compatibility)'
            ];
            
            $systemLanguage->update(['value' => json_encode($languages)]);
            echo "✅ Added 'ku' to system_language configuration" . PHP_EOL;
        } else {
            echo "✅ 'ku' already exists in system_language" . PHP_EOL;
        }
    }

    echo "🎯 Kurdish language backward compatibility setup complete!" . PHP_EOL;
    echo "📱 Flutter apps can now use 'ku' language code" . PHP_EOL;
    echo "🔄 Backend will automatically map 'ku' → 'ckb' for translations" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
}