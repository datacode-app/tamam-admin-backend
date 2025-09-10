<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Translation;

class MultilingualImportService
{
    /**
     * Supported languages for multilingual import
     * 
     * @var array
     */
    protected $supportedLanguages = [
        'ckb' => 'Kurdish Sorani',
        'ar' => 'Arabic'
    ];

    /**
     * Configuration for translatable fields per model
     * 
     * @var array
     */
    protected $translatableFields = [
        'Store' => ['name', 'address'],
        'Item' => ['name', 'description'],
        'Category' => ['name'],
        'Campaign' => ['title', 'description'],
        'ItemCampaign' => ['title', 'description'],
        'Banner' => ['title'],
        'Coupon' => ['title', 'details'],
        'Brand' => ['name'],
        'AddOn' => ['name'],
        'Unit' => ['unit'],
        'Attribute' => ['name'],
        'Zone' => ['name'],
        'Module' => ['module_name', 'description'],
        'ParcelCategory' => ['name', 'description'],
        'DMVehicle' => ['type', 'model'],
        'FlashSale' => ['title'],
        'SubscriptionPackage' => ['package_name', 'description'],
    ];

    /**
     * Process multilingual data from import
     * 
     * @param array $data Row data from CSV/Excel
     * @param string $modelClass Model class name (e.g., 'Store', 'Item')
     * @param int $recordId The ID of the created/updated record
     * @return array Array of translation records to insert
     */
    public function processMultilingualData(array $data, string $modelClass, int $recordId): array
    {
        $translations = [];
        
        // Get translatable fields for this model
        $fields = $this->getTranslatableFields($modelClass);
        if (empty($fields)) {
            return $translations; // No translatable fields for this model
        }
        
        foreach ($fields as $field) {
            foreach ($this->supportedLanguages as $langCode => $langName) {
                // Support aliases for language codes (e.g., 'sorani' as alias of 'ckb')
                $aliases = $this->getLangAliases($langCode);
                $foundForThisLang = false;

                foreach ($aliases as $alias) {
                    // Check for multilingual columns in various formats
                    $multilingualColumns = [
                        $field . '_' . $alias, // Standard: name_ckb, description_ar
                        ucfirst($field) . '_' . $alias, // Capitalized: Name_ckb, Description_ar
                    ];

                    // Add special case variations
                    $variations = $this->getColumnVariations($field, $alias);
                    $multilingualColumns = array_merge($multilingualColumns, $variations);

                    foreach ($multilingualColumns as $column) {
                        if (array_key_exists($column, $data) && !empty($data[$column])) {
                            $translations[] = [
                                'translationable_type' => "App\\Models\\{$modelClass}",
                                'translationable_id' => $recordId,
                                // Canonicalize locale to the main code (e.g., 'ckb')
                                'locale' => $langCode,
                                'key' => $field,
                                'value' => $data[$column],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                            $foundForThisLang = true;
                            break; // Use first found variation for this language
                        }
                    }

                    if ($foundForThisLang) {
                        break;
                    }
                }
            }
        }
        
        return $translations;
    }

    /**
     * Handle special column name variations
     * 
     * @param string $field Base field name
     * @param string $langCode Language code
     * @return array Array of possible column variations
     */
    private function getColumnVariations(string $field, string $langCode): array
    {
        $variations = [];
        
        // Special mappings for common field variations
        $fieldMappings = [
            'name' => ['storeName', 'itemName', 'categoryName', 'brandName', 'Name', 'StoreName', 'ItemName', 'CategoryName', 'BrandName'],
            'address' => ['Address', 'location', 'Location', 'ADDRESS', 'LOCATION'],
            'description' => ['Description', 'details', 'Details', 'DESCRIPTION', 'DETAILS'],
            'title' => ['Title', 'campaignTitle', 'CampaignTitle', 'TITLE'],
        ];
        
        if (isset($fieldMappings[$field])) {
            foreach ($fieldMappings[$field] as $variation) {
                $variations[] = $variation . '_' . $langCode;
            }
        }
        
        return $variations;
    }

    /**
     * Bulk insert translations
     * 
     * @param array $translations Array of translation records
     * @return bool Success status
     */
    public function bulkInsertTranslations(array $translations): bool
    {
        if (empty($translations)) {
            return true;
        }
        
        try {
            // Delete existing translations for these records to avoid duplicates
            $recordIds = array_unique(array_column($translations, 'translationable_id'));
            $modelTypes = array_unique(array_column($translations, 'translationable_type'));
            
            foreach ($modelTypes as $modelType) {
                Translation::where('translationable_type', $modelType)
                    ->whereIn('translationable_id', $recordIds)
                    ->delete();
            }
            
            // Insert new translations
            Translation::insert($translations);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Multilingual Import Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate multilingual template headers for a specific model
     * 
     * @param string $modelClass Model class name
     * @param array $baseHeaders Base CSV headers
     * @return array Enhanced headers with multilingual columns
     */
    public function generateMultilingualHeaders(string $modelClass, array $baseHeaders): array
    {
        $fields = $this->getTranslatableFields($modelClass);
        if (empty($fields)) {
            return $baseHeaders;
        }
        
        $multilingualHeaders = $baseHeaders;
        
        foreach ($fields as $field) {
            foreach ($this->supportedLanguages as $langCode => $langName) {
                // Use canonical language codes directly (e.g., 'ckb') in templates
                $displayCode = $this->getDisplayLangCode($langCode);
                $multilingualHeaders[] = $field . '_' . $displayCode;
            }
        }
        
        return $multilingualHeaders;
    }

    /**
     * Detect if import data contains multilingual columns
     * 
     * @param array $headers CSV/Excel headers
     * @param string $modelClass Model class name
     * @return bool True if multilingual columns detected
     */
    public function hasMultilingualColumns(array $headers, string $modelClass): bool
    {
        $fields = $this->getTranslatableFields($modelClass);
        if (empty($fields)) {
            return false;
        }
        
        foreach ($fields as $field) {
            foreach ($this->supportedLanguages as $langCode => $langName) {
                foreach ($this->getLangAliases($langCode) as $alias) {
                    $multilingualColumn = $field . '_' . $alias;
                    if (in_array($multilingualColumn, $headers, true)) {
                        return true;
                    }

                    // Check variations
                    foreach ($this->getColumnVariations($field, $alias) as $variation) {
                        if (in_array($variation, $headers, true)) {
                            return true;
                        }
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * Return supported alias codes for a canonical language code
     * Example: 'ckb' => ['ckb', 'CKB', 'kurdish', 'sorani']
     */
    public function getLangAliases(string $langCode): array
    {
        $aliases = [
            'ckb' => ['ckb', 'CKB', 'ckb_IQ', 'kurdish', 'sorani'],
            'ar' => ['ar', 'AR', 'ar_IQ', 'arabic'],
        ];
        return $aliases[$langCode] ?? [$langCode];
    }

    /**
     * Return user-facing display code for headers/templates
     * Example: 'ckb' => 'ckb' (Kurdish Sorani)
     */
    public function getDisplayLangCode(string $langCode): string
    {
        // Use the canonical language codes directly
        return $langCode;
    }

    /**
     * Get supported languages
     * 
     * @return array
     */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }

    /**
     * Get translatable fields for a model
     * 
     * @param string $modelClass
     * @return array
     */
    public function getTranslatableFields(string $modelClass): array
    {
        // Try full class name first, then try basename for backwards compatibility
        $baseName = class_basename($modelClass);
        return $this->translatableFields[$modelClass] ?? $this->translatableFields[$baseName] ?? [];
    }

    /**
     * Create example multilingual data for testing
     * 
     * @param string $modelClass
     * @return array Sample data with multilingual fields
     */
    public function createExampleData(string $modelClass): array
    {
        $examples = [
            'Store' => [
                [
                    'ownerFirstName' => 'Ahmad',
                    'ownerLastName' => 'Hassan', 
                    'storeName' => 'Kurdistan Restaurant',
                    'name_ckb' => 'چێشتخانەی کوردستان',
                    'name_ar' => 'مطعم كردستان',
                    'phone' => '+9647501234567',
                    'email' => 'ahmad.kurdistan@test.com',
                    'Address' => 'Downtown Erbil',
                    'address_ckb' => 'ناوەندی هەولێر', 
                    'address_ar' => 'وسط أربيل',
                    'zone_id' => 1,
                    'module_id' => 2,
                ]
            ],
            'Item' => [
                [
                    'name' => 'Chicken Biryani',
                    'name_ckb' => 'برنجی مریشک',
                    'name_ar' => 'برياني الدجاج',
                    'description' => 'Delicious aromatic chicken biryani with basmati rice and traditional spices',
                    'description_ckb' => 'برنجی مریشکی خۆشتام لەگەڵ برنجی باسماتی و بەهاراتی تەقلیدی',
                    'description_ar' => 'برياني دجاج لذيذ وعطر مع أرز البسمتي والبهارات التقليدية',
                    'category_id' => 1,
                    'store_id' => 1,
                    'price' => 15000,
                    'discount' => 10,
                    'discount_type' => 'percent',
                    'tax' => 5,
                    'status' => 1,
                ],
                [
                    'name' => 'Kurdish Kebab',
                    'name_ckb' => 'کەبابی کوردی',
                    'name_ar' => 'كباب كردي',
                    'description' => 'Traditional Kurdish grilled meat kebab with fresh vegetables and bread',
                    'description_ckb' => 'کەبابی گۆشتی برژاوی کوردی لەگەڵ سەوزە تازەکان و نان',
                    'description_ar' => 'كباب اللحم الكردي المشوي التقليدي مع الخضار الطازجة والخبز',
                    'category_id' => 1,
                    'store_id' => 1,
                    'price' => 18000,
                    'discount' => 0,
                    'discount_type' => 'amount',
                    'tax' => 5,
                    'status' => 1,
                ],
                [
                    'name' => 'Dolma (Stuffed Vegetables)',
                    'name_ckb' => 'دۆڵمە (سەوزەی پڕکراو)',
                    'name_ar' => 'دولما (خضار محشية)',
                    'description' => 'Traditional stuffed vegetables with rice, herbs, and spices',
                    'description_ckb' => 'سەوزەی پڕکراوی تەقلیدی لەگەڵ برنج و گیا و بەهارات',
                    'description_ar' => 'خضار محشية تقليدية بالأرز والأعشاب والبهارات',
                    'category_id' => 2,
                    'store_id' => 1,
                    'price' => 12000,
                    'discount' => 5,
                    'discount_type' => 'percent',
                    'tax' => 5,
                    'status' => 1,
                ],
                [
                    'name' => 'Baklava Dessert',
                    'name_ckb' => 'شیرینی بەقلاوە',
                    'name_ar' => 'حلوى البقلاوة',
                    'description' => 'Sweet layered pastry with nuts and honey syrup',
                    'description_ckb' => 'شیرینی چین چین لەگەڵ گوێز و شلی هەنگوین',
                    'description_ar' => 'معجنات حلوة متعددة الطبقات بالمكسرات وشراب العسل',
                    'category_id' => 3,
                    'store_id' => 2,
                    'price' => 8000,
                    'discount' => 0,
                    'discount_type' => 'amount',
                    'tax' => 5,
                    'status' => 1,
                ],
                [
                    'name' => 'Kurdish Tea (Chai)',
                    'name_ckb' => 'چای کوردی',
                    'name_ar' => 'شاي كردي',
                    'description' => 'Traditional Kurdish black tea served in glass cups with sugar',
                    'description_ckb' => 'چای ڕەشی تەقلیدی کوردی کە لە کوپی شووشەدا دەخرێتە ڕوو لەگەڵ شەکر',
                    'description_ar' => 'شاي أسود كردي تقليدي يُقدم في أكواب زجاجية مع السكر',
                    'category_id' => 4,
                    'store_id' => 3,
                    'price' => 2000,
                    'discount' => 0,
                    'discount_type' => 'amount',
                    'tax' => 0,
                    'status' => 1,
                ]
            ],
            'Category' => [
                [
                    'name' => 'Main Dishes',
                    'name_ckb' => 'خواردنە سەرەکییەکان', 
                    'name_ar' => 'الأطباق الرئيسية',
                    'parent_id' => 0,
                    'position' => 1,
                    'status' => 1,
                    'priority' => 1,
                    'module_id' => 2,
                ]
            ]
        ];
        
        return $examples[$modelClass] ?? [];
    }
}