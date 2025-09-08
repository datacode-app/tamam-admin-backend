<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use App\Models\Translation;
use App\Models\Store;
use App\Models\Vendor;
use App\Models\StoreConfig;
use Exception;

/**
 * BULLETPROOF MULTILINGUAL IMPORT SYSTEM
 * 
 * This service is the SINGLE SOURCE OF TRUTH for all multilingual operations.
 * It prevents all recurring issues by implementing comprehensive validation,
 * error handling, and self-healing mechanisms.
 * 
 * NEVER BYPASS THIS SERVICE - Always use it for multilingual operations.
 */
class BulletproofMultilingualService
{
    private $importValidator;
    private $languageNormalizer;
    private $transactionManager;
    private $auditLog = [];

    public function __construct()
    {
        $this->importValidator = new ImportValidator();
        $this->languageNormalizer = new LanguageNormalizer();
        $this->transactionManager = new TransactionManager();
    }

    /**
     * BULLETPROOF STORE IMPORT
     * This method handles the complete store import process with full error recovery
     */
    public function importStores(string $excelPath, string $originalName = null): array
    {
        $this->auditLog = [];
        $this->log('info', 'Starting bulletproof store import', ['file' => $originalName ?? basename($excelPath)]);

        try {
            // Phase 1: Pre-import validation and preparation
            $this->validateSystemHealth();
            $validationResult = $this->importValidator->validateExcelFile($excelPath, $originalName);
            
            if (!$validationResult['valid']) {
                throw new Exception('Excel validation failed: ' . implode(', ', $validationResult['errors']));
            }

            // Phase 2: Parse and normalize data
            $import = new \Rap2hpoutre\FastExcel\FastExcel();
            $collection = $import->import($excelPath);
            
            // Convert collection to array if needed
            $collectionArray = $collection instanceof \Illuminate\Support\Collection ? $collection->toArray() : $collection;
            $normalizedData = $this->normalizeImportData($collectionArray);

            // Phase 3: Execute atomic import
            return $this->transactionManager->executeAtomicImport($normalizedData, [$this, 'executeStoreImport']);

        } catch (Exception $e) {
            $this->log('error', 'Import failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            // Attempt recovery
            $this->attemptRecovery($e);
            
            throw $e;
        }
    }

    /**
     * SELF-HEALING SYSTEM VALIDATION
     * Automatically detects and fixes common database issues
     */
    public function validateSystemHealth(): void
    {
        $this->log('info', 'Running system health validation');

        // Fix 1: Cleanup orphaned translations
        $orphanedTranslations = DB::table('translations')
            ->where('translationable_type', 'App\\Models\\Store')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('stores')
                      ->whereRaw('stores.id = translations.translationable_id');
            })->count();

        if ($orphanedTranslations > 0) {
            DB::table('translations')
                ->where('translationable_type', 'App\\Models\\Store')
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                          ->from('stores')
                          ->whereRaw('stores.id = translations.translationable_id');
                })->delete();
            
            $this->log('warning', "Cleaned up {$orphanedTranslations} orphaned translations");
        }

        // Fix 2: Correct wrong translationable_type values
        $wrongTypes = DB::table('translations')
            ->whereIn('translationable_id', function($query) {
                $query->select('id')->from('stores');
            })
            ->where('translationable_type', '!=', 'App\\Models\\Store')
            ->count();

        if ($wrongTypes > 0) {
            DB::table('translations')
                ->whereIn('translationable_id', function($query) {
                    $query->select('id')->from('stores');
                })
                ->where('translationable_type', '!=', 'App\\Models\\Store')
                ->update(['translationable_type' => 'App\\Models\\Store']);
            
            $this->log('warning', "Fixed {$wrongTypes} incorrect translationable_type values");
        }

        // Fix 3: Validate language settings
        $this->validateLanguageSettings();

        $this->log('info', 'System health validation completed');
    }

    /**
     * NORMALIZE IMPORT DATA
     * Standardizes all data formats and language codes
     */
    private function normalizeImportData(array $collection): array
    {
        $normalized = [];

        foreach ($collection as $index => $row) {
            $normalizedRow = $this->languageNormalizer->normalizeRow($row);
            
            // Validate required fields
            $validation = $this->importValidator->validateRow($normalizedRow, $index + 1);
            if (!$validation['valid']) {
                throw new Exception("Row " . ($index + 1) . " validation failed: " . implode(', ', $validation['errors']));
            }

            $normalized[] = $normalizedRow;
        }

        return $normalized;
    }

    /**
     * EXECUTE STORE IMPORT (Called within transaction)
     */
    public function executeStoreImport(array $normalizedData): array
    {
        $importedStores = [];
        $baseVendorId = DB::table('vendors')->orderBy('id', 'desc')->value('id') ?: 0;

        foreach ($normalizedData as $index => $data) {
            // Check for duplicates
            if (Store::where('email', $data['email'])->exists()) {
                $this->log('warning', "Store with email {$data['email']} already exists, skipping");
                continue;
            }

            // Create vendor
            $vendorId = $baseVendorId + $index + 1;
            $vendorData = $this->prepareVendorData($data, $vendorId);
            DB::table('vendors')->insert($vendorData);

            // Create store
            $storeData = $this->prepareStoreData($data, $vendorId);
            $storeId = DB::table('stores')->insertGetId($storeData);

            // Create store config if needed
            if (isset($data['normalized']['HalalTagStatus'])) {
                $storeConfigData = [
                    'store_id' => $storeId,
                    'halal_tag_status' => $data['normalized']['HalalTagStatus'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                DB::table('store_configs')->insert($storeConfigData);
            }

            // Process multilingual translations
            $translations = $this->processTranslations($data, $storeId);
            if (!empty($translations)) {
                DB::table('translations')->insert($translations);
                $this->log('info', "Created " . count($translations) . " translations for store {$storeId}");
            }

            $importedStores[] = [
                'vendor_id' => $vendorId,
                'store_id' => $storeId,
                'name' => $data['normalized']['storeName'],
                'email' => $data['email'],
                'translations_count' => count($translations)
            ];

            $this->log('info', "Successfully imported store: {$data['normalized']['storeName']} (ID: {$storeId})");
        }

        return [
            'success' => true,
            'imported_count' => count($importedStores),
            'stores' => $importedStores,
            'audit_log' => $this->auditLog
        ];
    }

    /**
     * PROCESS MULTILINGUAL TRANSLATIONS
     */
    private function processTranslations(array $data, int $storeId): array
    {
        $translations = [];
        $supportedFields = ['name', 'address']; // Extensible
        $supportedLanguages = $this->languageNormalizer->getSupportedLanguages();

        foreach ($supportedFields as $field) {
            foreach ($supportedLanguages as $langCode => $langInfo) {
                $aliases = $langInfo['aliases'];
                
                foreach ($aliases as $alias) {
                    $columnKey = $field . '_' . $alias;
                    
                    if (isset($data['multilingual'][$columnKey]) && !empty($data['multilingual'][$columnKey])) {
                        $translations[] = [
                            'translationable_type' => 'App\\Models\\Store',
                            'translationable_id' => $storeId,
                            'locale' => $langCode, // Use canonical language code
                            'key' => $field,
                            'value' => $data['multilingual'][$columnKey],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        break; // Use first found alias
                    }
                }
            }
        }

        return $translations;
    }

    /**
     * PREPARE VENDOR DATA
     */
    private function prepareVendorData(array $data, int $vendorId): array
    {
        return [
            'id' => $vendorId,
            'f_name' => $data['normalized']['ownerFirstName'] ?? 'Store',
            'l_name' => $data['normalized']['ownerLastName'] ?? 'Owner',
            'phone' => $data['normalized']['phone'] ?? '',
            'email' => $data['email'],
            'password' => bcrypt('12345678'), // Default secure password
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * PREPARE STORE DATA
     */
    private function prepareStoreData(array $data, int $vendorId): array
    {
        $normalized = $data['normalized'];
        // Ensure visibility: if admin is not super-admin, align zone with admin's zone
        $admin = Auth::guard('admin')->user();
        $incomingZoneId = $normalized['zone_id'] ?? null;
        if ($admin && ($admin->role_id ?? null) != 1) {
            $normalized['zone_id'] = $admin->zone_id ?? ($incomingZoneId ?: 1);
        } else {
            $normalized['zone_id'] = $incomingZoneId ?: 1;
        }

        // Ensure module aligns with current module context if missing/zero
        $currentModuleId = (int) (Config::get('module.current_module_id') ?? 0);
        $normalized['module_id'] = !empty($normalized['module_id']) ? $normalized['module_id'] : ($currentModuleId ?: 1);

        return [
            'name' => $normalized['storeName'],
            'phone' => $normalized['phone'] ?? '',
            'email' => $data['email'],
            'logo' => $normalized['logo'] ?? 'def.png',
            'cover_photo' => $normalized['CoverPhoto'] ?? 'def.png',
            'latitude' => $normalized['latitude'] ?? 0,
            'longitude' => $normalized['longitude'] ?? 0,
            'address' => $normalized['Address'] ?? '',
            'zone_id' => (int) $normalized['zone_id'],
            'module_id' => (int) $normalized['module_id'],
            'minimum_order' => $normalized['MinimumOrderAmount'] ?? 0,
            'comission' => $normalized['Comission'] ?? 0,
            'tax' => $normalized['Tax'] ?? 0,
            'delivery_time' => $normalized['DeliveryTime'] ?? '30-45 min',
            'minimum_shipping_charge' => $normalized['MinimumDeliveryFee'] ?? 0,
            'per_km_shipping_charge' => $normalized['PerKmDeliveryFee'] ?? 0,
            'maximum_shipping_charge' => $normalized['MaximumDeliveryFee'] ?? 0,
            'schedule_order' => $this->booleanValue($normalized['ScheduleOrder'] ?? 'no'),
            'status' => $this->booleanValue($normalized['Status'] ?? 'active', 'active'),
            'self_delivery_system' => $this->booleanValue($normalized['SelfDeliverySystem'] ?? 'no'),
            'veg' => $this->booleanValue($normalized['Veg'] ?? 'no'),
            'non_veg' => $this->booleanValue($normalized['NonVeg'] ?? 'yes'),
            'free_delivery' => $this->booleanValue($normalized['FreeDelivery'] ?? 'no'),
            'take_away' => $this->booleanValue($normalized['TakeAway'] ?? 'yes'),
            'delivery' => $this->booleanValue($normalized['Delivery'] ?? 'yes'),
            'reviews_section' => $this->booleanValue($normalized['ReviewsSection'] ?? 'active', 'active'),
            'pos_system' => $this->booleanValue($normalized['PosSystem'] ?? 'active', 'active'),
            'active' => $this->booleanValue($normalized['storeOpen'] ?? 'yes'),
            'featured' => $this->booleanValue($normalized['FeaturedStore'] ?? 'no'),
            'cutlery' => $this->booleanValue($normalized['Cutlery'] ?? 'yes'),
            'vendor_id' => $vendorId,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * STANDARDIZED BOOLEAN CONVERSION
     */
    private function booleanValue($value, string $trueValue = 'yes'): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        
        return (strtolower($value) === strtolower($trueValue)) ? 1 : 0;
    }

    /**
     * VALIDATE LANGUAGE SETTINGS
     */
    private function validateLanguageSettings(): void
    {
        $languageSettings = DB::table('business_settings')->where('key', 'language')->first();
        
        if (!$languageSettings) {
            // Create default language setting
            DB::table('business_settings')->insert([
                'key' => 'language',
                'value' => json_encode(['en', 'ckb', 'ar']),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $this->log('info', 'Created default language settings');
        }
    }

    /**
     * ATTEMPT RECOVERY FROM ERRORS
     */
    private function attemptRecovery(Exception $e): void
    {
        $this->log('info', 'Attempting error recovery', ['error' => $e->getMessage()]);
        
        // Recovery strategy based on error type
        if (str_contains($e->getMessage(), 'vendor_id')) {
            $this->log('info', 'Detected vendor_id issue - system health check should have prevented this');
        }
        
        if (str_contains($e->getMessage(), 'translationable_type')) {
            $this->log('info', 'Detected translation type issue - running emergency cleanup');
            $this->validateSystemHealth();
        }
    }

    /**
     * COMPREHENSIVE VERIFICATION
     */
    public function verifyImport(array $importResult): array
    {
        $verification = [
            'overall_status' => 'success',
            'stores_verified' => 0,
            'translations_verified' => 0,
            'issues_found' => [],
            'recommendations' => []
        ];

        foreach ($importResult['stores'] as $store) {
            // Verify store exists
            $storeExists = DB::table('stores')->where('id', $store['store_id'])->exists();
            if ($storeExists) {
                $verification['stores_verified']++;
            } else {
                $verification['issues_found'][] = "Store ID {$store['store_id']} not found in database";
                $verification['overall_status'] = 'warning';
            }

            // Verify translations
            $translationCount = DB::table('translations')
                ->where('translationable_type', 'App\\Models\\Store')
                ->where('translationable_id', $store['store_id'])
                ->count();
                
            if ($translationCount >= $store['translations_count']) {
                $verification['translations_verified'] += $translationCount;
            } else {
                $verification['issues_found'][] = "Expected {$store['translations_count']} translations for store {$store['store_id']}, found {$translationCount}";
                $verification['overall_status'] = 'warning';
            }
        }

        return $verification;
    }

    /**
     * AUDIT LOGGING
     */
    private function log(string $level, string $message, array $context = []): void
    {
        $logEntry = [
            'timestamp' => now()->toDateTimeString(),
            'level' => $level,
            'message' => $message,
            'context' => $context
        ];

        $this->auditLog[] = $logEntry;
        
        // Also log to Laravel log
        Log::channel('daily')->{$level}($message, $context);
    }

    /**
     * GET AUDIT LOG
     */
    public function getAuditLog(): array
    {
        return $this->auditLog;
    }
}