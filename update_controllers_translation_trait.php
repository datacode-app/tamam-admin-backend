<?php

/**
 * CONTROLLER TRANSLATION LOADING FIX
 * 
 * This script updates critical controllers to use the bulletproof TranslationLoadingTrait
 * instead of problematic with('translations') eager loading that fails silently.
 * 
 * Priority Controllers (most critical for translation display):
 * - Admin: VendorController, ItemController, BrandController, etc.
 * - Vendor: ItemController, RestaurantController, BannerController, etc.
 * 
 * Usage: php update_controllers_translation_trait.php [--dry-run] [--controller=specific]
 */

class ControllerTranslationUpdater
{
    private $dryRun = false;
    private $specificController = null;
    private $updatedControllers = [];
    private $errors = [];
    private $backupDir;
    
    // Priority controllers that need immediate fixing
    private $priorityControllers = [
        // Admin Controllers
        'app/Http/Controllers/Admin/VendorController.php',
        'app/Http/Controllers/Admin/ItemController.php',
        'app/Http/Controllers/Admin/BrandController.php',
        'app/Http/Controllers/Admin/CategoryController.php',
        'app/Http/Controllers/Admin/UnitController.php',
        'app/Http/Controllers/Admin/ZoneController.php',
        'app/Http/Controllers/Admin/CouponController.php',
        'app/Http/Controllers/Admin/BannerController.php',
        'app/Http/Controllers/Admin/Promotion/AdvertisementController.php',
        
        // Vendor Controllers
        'app/Http/Controllers/Vendor/ItemController.php',
        'app/Http/Controllers/Vendor/RestaurantController.php',
        'app/Http/Controllers/Vendor/BannerController.php',
        'app/Http/Controllers/Vendor/AddOnController.php',
        'app/Http/Controllers/Vendor/CouponController.php',
        'app/Http/Controllers/Vendor/BusinessSettingsController.php'
    ];
    
    public function __construct()
    {
        $this->parseArgs();
        $this->createBackupDirectory();
    }
    
    private function parseArgs()
    {
        global $argv;
        $this->dryRun = in_array('--dry-run', $argv);
        
        foreach ($argv as $arg) {
            if (strpos($arg, '--controller=') === 0) {
                $this->specificController = substr($arg, 13);
            }
        }
    }
    
    private function createBackupDirectory()
    {
        $this->backupDir = 'controller_fix_backup_' . date('Ymd_His');
        if (!$this->dryRun && !file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    public function run()
    {
        echo "\n🔧 CONTROLLER TRANSLATION LOADING FIX\n";
        echo "====================================\n\n";
        
        if ($this->dryRun) {
            echo "🔍 DRY RUN MODE - No files will be modified\n\n";
        }
        
        if ($this->specificController) {
            echo "🎯 Updating specific controller: {$this->specificController}\n\n";
            $this->updateController($this->specificController);
        } else {
            $this->updatePriorityControllers();
        }
        
        $this->generateReport();
    }
    
    private function updatePriorityControllers()
    {
        echo "🎯 Updating priority controllers with translation issues...\n\n";
        
        foreach ($this->priorityControllers as $controller) {
            if (file_exists($controller)) {
                $this->updateController($controller);
            } else {
                echo "⚠️  Controller not found: $controller\n";
            }
        }
    }
    
    private function updateController($controllerPath)
    {
        echo "🔧 Updating: " . basename($controllerPath) . "\n";
        
        $content = file_get_contents($controllerPath);
        $originalContent = $content;
        
        // Check if TranslationLoadingTrait is already imported
        $hasTraitImport = strpos($content, 'use App\Traits\TranslationLoadingTrait;') !== false;
        $hasTraitUse = strpos($content, 'use TranslationLoadingTrait;') !== false;
        
        // Add trait import if not present
        if (!$hasTraitImport) {
            $content = $this->addTraitImport($content);
            echo "  ✅ Added TranslationLoadingTrait import\n";
        }
        
        // Add trait use in class if not present
        if (!$hasTraitUse) {
            $content = $this->addTraitUse($content);
            echo "  ✅ Added trait use in class\n";
        }
        
        // Fix edit methods that use problematic with('translations')
        $content = $this->fixEditMethods($content, $controllerPath);
        
        if ($content !== $originalContent) {
            if (!$this->dryRun) {
                // Create backup
                $backupFile = $this->backupDir . '/' . str_replace(['/', '\\'], '_', $controllerPath) . '.backup';
                copy($controllerPath, $backupFile);
                
                // Write updated content
                file_put_contents($controllerPath, $content);
            }
            
            $this->updatedControllers[] = $controllerPath;
            echo "  ✅ UPDATED: " . basename($controllerPath) . "\n\n";
        } else {
            echo "  ℹ️  No changes needed for: " . basename($controllerPath) . "\n\n";
        }
    }
    
    private function addTraitImport($content)
    {
        // Find the last use statement and add our trait import after it
        $lines = explode("\n", $content);
        $lastUseIndex = -1;
        
        for ($i = 0; $i < count($lines); $i++) {
            if (preg_match('/^use\s+[^;]+;/', trim($lines[$i]))) {
                $lastUseIndex = $i;
            }
        }
        
        if ($lastUseIndex >= 0) {
            array_splice($lines, $lastUseIndex + 1, 0, 'use App\Traits\TranslationLoadingTrait;');
        } else {
            // If no use statements found, add after namespace
            for ($i = 0; $i < count($lines); $i++) {
                if (preg_match('/^namespace\s+/', trim($lines[$i]))) {
                    array_splice($lines, $i + 2, 0, ['', 'use App\Traits\TranslationLoadingTrait;']);
                    break;
                }
            }
        }
        
        return implode("\n", $lines);
    }
    
    private function addTraitUse($content)
    {
        // Find the class declaration and add trait use
        $lines = explode("\n", $content);
        
        for ($i = 0; $i < count($lines); $i++) {
            if (preg_match('/^class\s+\w+.*\{/', trim($lines[$i]))) {
                // Add trait use right after class opening brace
                array_splice($lines, $i + 1, 0, ['', '    use TranslationLoadingTrait;', '']);
                break;
            }
        }
        
        return implode("\n", $lines);
    }
    
    private function fixEditMethods($content, $controllerPath)
    {
        // Find edit methods that use problematic with('translations') patterns
        $patterns = [
            // Pattern 1: Model::with('translations')->findOrFail($id)
            '/(\$\w+)\s*=\s*(\w+)::with\(\[?[\'"]translations[\'"].*?\]\)?->findOrFail\((\$?\w+)\);/',
            // Pattern 2: Model::with(['translations'])->findOrFail($id) 
            '/(\$\w+)\s*=\s*(\w+)::with\(\[\s*[\'"]translations[\'"]\s*\]\)->findOrFail\((\$?\w+)\);/',
            // Pattern 3: More complex with relationships
            '/(\$\w+)\s*=\s*(\w+)::with\(\[[^\]]*[\'"]translations[\'"][^\]]*\]\)->findOrFail\((\$?\w+)\);/'
        ];
        
        foreach ($patterns as $pattern) {
            $content = preg_replace_callback($pattern, function($matches) use ($controllerPath) {
                $varName = $matches[1];
                $modelName = $matches[2];
                $idVar = $matches[3];
                
                // Determine the full model class name
                $modelClass = $this->getModelClassName($modelName, $controllerPath);
                
                $replacement = "{$varName} = \$this->loadWithTranslations('{$modelClass}', {$idVar});";
                
                echo "    🔄 Replaced problematic with('translations') with bulletproof loading\n";
                return $replacement;
            }, $content);
        }
        
        return $content;
    }
    
    private function getModelClassName($modelName, $controllerPath)
    {
        // Map common model names to their full class names
        $modelMap = [
            'Store' => 'App\\Models\\Store',
            'Item' => 'App\\Models\\Item',
            'Brand' => 'App\\Models\\Brand',
            'Category' => 'App\\Models\\Category',
            'Unit' => 'App\\Models\\Unit',
            'Zone' => 'App\\Models\\Zone',
            'Coupon' => 'App\\Models\\Coupon',
            'Banner' => 'App\\Models\\Banner',
            'Advertisement' => 'App\\Models\\Advertisement',
            'AddOn' => 'App\\Models\\AddOn'
        ];
        
        return $modelMap[$modelName] ?? "App\\Models\\{$modelName}";
    }
    
    private function generateReport()
    {
        echo "\n📊 CONTROLLER UPDATE REPORT\n";
        echo "===========================\n";
        echo "Controllers analyzed: " . count($this->priorityControllers) . "\n";
        echo "Controllers updated: " . count($this->updatedControllers) . "\n";
        echo "Errors: " . count($this->errors) . "\n";
        
        if (!$this->dryRun && $this->updatedControllers) {
            echo "Backup directory: " . $this->backupDir . "\n";
        }
        
        if ($this->updatedControllers) {
            echo "\n✅ UPDATED CONTROLLERS:\n";
            foreach ($this->updatedControllers as $controller) {
                echo "  - " . basename($controller) . "\n";
            }
        }
        
        if ($this->errors) {
            echo "\n❌ ERRORS:\n";
            foreach ($this->errors as $error) {
                echo "  - $error\n";
            }
        }
        
        echo "\n🎯 VERIFICATION STEPS:\n";
        echo "1. Test admin panel edit forms (stores, items, brands, etc.)\n";
        echo "2. Test vendor panel edit forms (items, settings, banners, etc.)\n";
        echo "3. Verify Kurdish (ckb) and Arabic (ar) translations display correctly\n";
        echo "4. Check translation form fields are populated after page reload\n";
        echo "5. Test translation saving and persistence\n";
        
        echo "\n🧪 RECOMMENDED TESTING:\n";
        echo "php test_translation_fixes.php\n";
    }
}

// Run the script
if (php_sapi_name() === 'cli') {
    $updater = new ControllerTranslationUpdater();
    $updater->run();
} else {
    echo "This script must be run from command line\n";
}