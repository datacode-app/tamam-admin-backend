<?php

namespace App\Services;

/**
 * LANGUAGE NORMALIZER
 * 
 * Single source of truth for all language code handling.
 * Prevents language-related issues by standardizing all language operations.
 */
class LanguageNormalizer
{
    /**
     * CANONICAL LANGUAGE CONFIGURATION
     * This is the master configuration for ALL language handling in the system.
     */
    private $languageConfig = [
        'ckb' => [
            'name' => 'Kurdish Sorani',
            'native_name' => 'کوردی سۆرانی',
            'canonical' => 'ckb',
            'aliases' => ['ckb', 'CKB', 'ckb_IQ', 'kurdish', 'sorani', 'Kurdish'],
            'rtl' => true,
            'fallbacks' => ['en']
        ],
        'ar' => [
            'name' => 'Arabic',
            'native_name' => 'العربية',
            'canonical' => 'ar', 
            'aliases' => ['ar', 'AR', 'ar_IQ', 'arabic', 'Arabic'],
            'rtl' => true,
            'fallbacks' => ['en']
        ],
        'en' => [
            'name' => 'English',
            'native_name' => 'English',
            'canonical' => 'en',
            'aliases' => ['en', 'EN', 'english', 'English'],
            'rtl' => false,
            'fallbacks' => []
        ]
    ];

    /**
     * NORMALIZE IMPORT ROW
     * Standardizes all data in an import row
     */
    public function normalizeRow(array $row): array
    {
        return [
            'email' => strtolower(trim($row['email'] ?? '')),
            'normalized' => $this->normalizeStandardFields($row),
            'multilingual' => $this->extractMultilingualFields($row)
        ];
    }

    /**
     * NORMALIZE STANDARD FIELDS
     */
    private function normalizeStandardFields(array $row): array
    {
        $normalized = [];

        // String fields - trim and clean
        $stringFields = [
            'ownerFirstName', 'ownerLastName', 'storeName', 'phone', 'email',
            'logo', 'CoverPhoto', 'Address', 'DeliveryTime'
        ];

        // Accept common header aliases
        $aliases = [
            'ownerFirstName' => ['owner_first_name','Owner First Name','OwnerFirstName','first_name','firstName'],
            'ownerLastName'  => ['owner_last_name','Owner Last Name','OwnerLastName','last_name','lastName'],
            'storeName'      => ['store_name','Store Name','StoreName','name','Name','store'],
            'phone'          => ['Phone','phone_number','PhoneNumber','mobile','Mobile'],
            'email'          => ['Email','email_address','EmailAddress'],
            'Address'        => ['address','Address ','location','Location','storeAddress','Store Address'],
            'DeliveryTime'   => ['delivery_time','Delivery Time','Delivery_Time','deliveryTime'],
        ];
        foreach ($stringFields as $field) {
            $value = $row[$field] ?? null;
            if ($value === null) {
                foreach ($aliases[$field] ?? [] as $alias) {
                    if (array_key_exists($alias, $row)) { $value = $row[$alias]; break; }
                }
            }
            $normalized[$field] = isset($value) ? trim((string)$value) : '';
        }

        // Numeric fields - ensure proper conversion
        $numericFields = [
            'latitude' => 0, 'longitude' => 0, 'zone_id' => 1, 'module_id' => 1,
            'MinimumOrderAmount' => 0, 'Comission' => 0, 'Tax' => 0,
            'MinimumDeliveryFee' => 0, 'PerKmDeliveryFee' => 0, 'MaximumDeliveryFee' => 0
        ];

        $numericAliases = [
            'zone_id' => ['zoneid','zoneId','Zone ID','zone','Zone'],
            'module_id' => ['moduleid','moduleId','Module ID','module','Module'],
        ];
        foreach ($numericFields as $field => $default) {
            $value = $row[$field] ?? null;
            if ($value === null) {
                foreach ($numericAliases[$field] ?? [] as $alias) {
                    if (array_key_exists($alias, $row)) { $value = $row[$alias]; break; }
                }
            }
            $value = $value ?? $default;
            $normalized[$field] = is_numeric($value) ? (float) $value : $default;
        }

        // Boolean fields - standardize all possible variations
        $booleanFields = [
            'ScheduleOrder' => 'no', 'Status' => 'active', 'SelfDeliverySystem' => 'no',
            'Veg' => 'no', 'NonVeg' => 'yes', 'FreeDelivery' => 'no',
            'TakeAway' => 'yes', 'Delivery' => 'yes', 'ReviewsSection' => 'active',
            'PosSystem' => 'active', 'storeOpen' => 'yes', 'FeaturedStore' => 'no'
        ];

        foreach ($booleanFields as $field => $default) {
            $value = strtolower(trim($row[$field] ?? $default));
            $normalized[$field] = $this->normalizeBooleanValue($value);
        }

        // Special handling for HalalTagStatus and Cutlery
        $normalized['HalalTagStatus'] = $this->normalizeNumericBoolean($row['HalalTagStatus'] ?? 0);
        $normalized['Cutlery'] = $this->normalizeNumericBoolean($row['Cutlery'] ?? 1);

        return $normalized;
    }

    /**
     * EXTRACT MULTILINGUAL FIELDS
     */
    private function extractMultilingualFields(array $row): array
    {
        $multilingual = [];

        // Get all possible multilingual column variations
        $multilingualPatterns = [
            'name' => ['name_', 'Name_', 'storeName_', 'StoreName_'],
            'address' => ['address_', 'Address_', 'location_', 'Location_'],
            'description' => ['description_', 'Description_', 'details_', 'Details_']
        ];

        foreach ($row as $column => $value) {
            if (empty(trim($value))) continue;

            // Check if this column matches any multilingual pattern
            foreach ($multilingualPatterns as $field => $patterns) {
                foreach ($patterns as $pattern) {
                    if (strpos($column, $pattern) === 0) {
                        $multilingual[$column] = trim($value);
                        break 2;
                    }
                }
            }

            // Also capture direct language suffix patterns
            foreach ($this->languageConfig as $langCode => $config) {
                foreach ($config['aliases'] as $alias) {
                    if (preg_match('/(.+)_' . preg_quote($alias, '/') . '$/i', $column, $matches)) {
                        $multilingual[$column] = trim($value);
                        break 2;
                    }
                }
            }
        }

        return $multilingual;
    }

    /**
     * NORMALIZE BOOLEAN VALUE
     */
    private function normalizeBooleanValue(string $value): string
    {
        $value = strtolower(trim($value));
        
        $trueValues = ['yes', 'true', '1', 'active', 'enable', 'enabled', 'on'];
        $falseValues = ['no', 'false', '0', 'inactive', 'disable', 'disabled', 'off'];

        if (in_array($value, $trueValues)) {
            return 'yes';
        }
        
        if (in_array($value, $falseValues)) {
            return 'no';
        }

        // Default based on common patterns
        return ($value === 'active' || $value === 'yes') ? 'yes' : 'no';
    }

    /**
     * NORMALIZE NUMERIC BOOLEAN
     */
    private function normalizeNumericBoolean($value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        $stringValue = strtolower(trim($value));
        $trueValues = ['yes', 'true', '1', 'active', 'enable', 'enabled', 'on'];
        
        return in_array($stringValue, $trueValues) ? 1 : 0;
    }

    /**
     * CANONICALIZE LANGUAGE CODE
     * Convert any language alias to its canonical form
     */
    public function canonicalizeLanguageCode(string $langCode): ?string
    {
        $normalizedInput = strtolower(trim($langCode));

        foreach ($this->languageConfig as $canonical => $config) {
            $normalizedAliases = array_map('strtolower', $config['aliases']);
            if (in_array($normalizedInput, $normalizedAliases)) {
                return $canonical;
            }
        }

        return null;
    }

    /**
     * GET SUPPORTED LANGUAGES
     */
    public function getSupportedLanguages(): array
    {
        return $this->languageConfig;
    }

    /**
     * GET LANGUAGE INFO
     */
    public function getLanguageInfo(string $langCode): ?array
    {
        $canonical = $this->canonicalizeLanguageCode($langCode);
        return $canonical ? $this->languageConfig[$canonical] : null;
    }

    /**
     * IS RTL LANGUAGE
     */
    public function isRtlLanguage(string $langCode): bool
    {
        $info = $this->getLanguageInfo($langCode);
        return $info ? $info['rtl'] : false;
    }

    /**
     * GET DISPLAY NAME
     */
    public function getDisplayName(string $langCode, bool $native = false): string
    {
        $info = $this->getLanguageInfo($langCode);
        if (!$info) return $langCode;

        return $native ? $info['native_name'] : $info['name'];
    }

    /**
     * VALIDATE LANGUAGE CODE
     */
    public function isValidLanguageCode(string $langCode): bool
    {
        return $this->canonicalizeLanguageCode($langCode) !== null;
    }

    /**
     * GET FALLBACK LANGUAGES
     */
    public function getFallbackLanguages(string $langCode): array
    {
        $info = $this->getLanguageInfo($langCode);
        return $info ? $info['fallbacks'] : ['en'];
    }

    /**
     * GENERATE LANGUAGE TEMPLATE HEADERS
     */
    public function generateTemplateHeaders(array $baseFields): array
    {
        $headers = [];

        // Add base fields
        foreach ($baseFields as $field) {
            $headers[] = $field;
        }

        // Add multilingual fields
        $multilingualFields = ['name', 'address'];
        
        foreach ($multilingualFields as $field) {
            foreach (['ckb', 'ar'] as $langCode) {
                $config = $this->languageConfig[$langCode];
                $displayAlias = $langCode;
                $headers[] = $field . '_' . $displayAlias;
            }
        }

        return $headers;
    }
}