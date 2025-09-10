<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MultilingualExportService
{
    protected $supportedLanguages = [
        'en' => 'English',
        'ar' => 'Arabic',
        'ckb' => 'Kurdish Sorani'
    ];

    protected $kurdishAliases = ['ckb', 'sorani', 'kurdish'];

    /**
     * Extract multilingual data from a model for export
     */
    public function extractMultilingualData($model, string $entityType): array
    {
        $multilingualData = [];
        
        // Get ALL translations directly from database, bypassing global scopes
        $translations = \DB::table('translations')
            ->where('translationable_type', get_class($model))
            ->where('translationable_id', $model->id)
            ->where('is_active', true)
            ->get(['locale', 'key', 'value'])
            ->toArray();
        
        if (!$translations || (is_countable($translations) && count($translations) === 0)) {
            // No translations exist - create empty multilingual fields
            $translatableFields = $this->getTranslatableFields($entityType);
            foreach ($translatableFields as $field) {
                foreach ($this->supportedLanguages as $langCode => $langName) {
                    $displayCode = $this->getDisplayLangCode($langCode);
                    $fieldKey = $field . '_' . $displayCode;
                    $multilingualData[$fieldKey] = null;
                }
            }
            return $multilingualData;
        }
        
        // Process existing translations (direct database results)
        $groupedTranslations = [];
        foreach ($translations as $translation) {
            $key = $translation->key ?? '';
            $locale = $translation->locale ?? '';
            $value = $translation->value ?? '';
            
            if ($key && $locale) {
                $groupedTranslations[$key][$locale] = $value;
            }
        }
        
        // Extract multilingual fields
        $translatableFields = $this->getTranslatableFields($entityType);
        foreach ($translatableFields as $field) {
            foreach ($this->supportedLanguages as $langCode => $langName) {
                $displayCode = $this->getDisplayLangCode($langCode);
                $fieldKey = $field . '_' . $displayCode;
                
                $translationValue = $this->getTranslationValue($groupedTranslations, $field, $langCode);
                $multilingualData[$fieldKey] = $translationValue;
            }
        }
        
        return $multilingualData;
    }

    /**
     * Get translation value with Kurdish fallback logic
     */
    protected function getTranslationValue(array $groupedTranslations, string $field, string $langCode): ?string
    {
        if (!isset($groupedTranslations[$field])) {
            return null;
        }
        
        $fieldTranslations = $groupedTranslations[$field];
        
        // Direct match
        if (isset($fieldTranslations[$langCode]) && !empty($fieldTranslations[$langCode])) {
            return $fieldTranslations[$langCode];
        }
        
        // Kurdish fallback logic
        if ($langCode === 'ckb' || in_array($langCode, $this->kurdishAliases)) {
            return $this->getKurdishTranslation($fieldTranslations);
        }
        
        return null;
    }

    /**
     * Get Kurdish translation with fallback aliases
     */
    protected function getKurdishTranslation(array $fieldTranslations): ?string
    {
        // Try each Kurdish alias in order of preference
        $kurdishPreference = ['ckb', 'sorani', 'kurdish'];
        
        foreach ($kurdishPreference as $alias) {
            if (isset($fieldTranslations[$alias]) && !empty($fieldTranslations[$alias])) {
                return $fieldTranslations[$alias];
            }
        }
        
        return null;
    }

    /**
     * Get translatable fields for entity type
     */
    protected function getTranslatableFields(string $entityType): array
    {
        $fieldMap = [
            'Store' => ['name', 'address'],
            'Item' => ['name', 'description'],
            'Category' => ['name'],
            'Banner' => ['title'],
            'Coupon' => ['title', 'details']
        ];
        
        return $fieldMap[$entityType] ?? [];
    }

    /**
     * Get display language code for export headers
     */
    protected function getDisplayLangCode(string $langCode): string
    {
        // Use ckb for Kurdish Sorani directly
        return $langCode;
    }

    /**
     * Get supported languages configuration
     */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }

    /**
     * Check if entity has any translations
     */
    public function hasTranslations($model): bool
    {
        $translations = $model->translations ?? null;
        return $translations && (is_countable($translations) ? count($translations) > 0 : !empty($translations));
    }
}