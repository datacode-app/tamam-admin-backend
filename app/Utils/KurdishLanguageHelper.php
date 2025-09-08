<?php

namespace App\Utils;

class KurdishLanguageHelper
{
    /**
     * All supported Kurdish language code variants
     * These will all be normalized to 'ckb' for backend processing
     */
    const KURDISH_VARIANTS = [
        'ku',                // Kurdish (generic)
        'ckb',               // Central Kurdish/Sorani (ISO 639-3)
        'kmr',               // Kurmanji Kurdish  
        'kur',               // Kurdish (alternative)
        'kurdish',           // Full name variant
        'sorani',            // Kurdish Sorani dialect
        'central-kurdish',   // Central Kurdish
        'kurdish-sorani',    // Kurdish Sorani combined
    ];

    /**
     * Check if a language code represents Kurdish in any variant
     * 
     * @param string|null $languageCode
     * @return bool
     */
    public static function isKurdishLanguage(?string $languageCode): bool
    {
        if ($languageCode === null) {
            return false;
        }
        
        return in_array(strtolower($languageCode), self::KURDISH_VARIANTS);
    }

    /**
     * Normalize any Kurdish variant to the backend standard 'ckb'
     * 
     * @param string $languageCode
     * @return string
     */
    public static function normalizeKurdishToBackend(string $languageCode): string
    {
        return self::isKurdishLanguage($languageCode) ? 'ckb' : $languageCode;
    }

    /**
     * Get the canonical Kurdish translation file path
     * 
     * @return string
     */
    public static function getKurdishTranslationPath(): string
    {
        return 'ckb';
    }

    /**
     * Get display name for Kurdish language
     * 
     * @return string
     */
    public static function getKurdishDisplayName(): string
    {
        return 'کوردی';
    }

    /**
     * Get all supported Kurdish variants for documentation/debugging
     * 
     * @return array
     */
    public static function getSupportedVariants(): array
    {
        return self::KURDISH_VARIANTS;
    }
}