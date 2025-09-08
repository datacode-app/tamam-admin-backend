<?php

namespace App\Traits;

trait HasKurdishFallback
{
    /**
     * Kurdish locale variants for fallback
     */
    private static $kurdishLocales = ['ku', 'ckb', 'kmr', 'kurdish', 'sorani'];

    /**
     * Get translated attribute with Kurdish fallback
     */
    public function getTranslatedAttribute(string $key, $defaultValue = null)
    {
        // First try current locale
        if (count($this->translations ?? []) > 0) {
            foreach ($this->translations as $translation) {
                if ($translation['key'] == $key) {
                    return $translation['value'];
                }
            }
        }

        // Kurdish fallback: try other Kurdish variants
        $currentLocale = app()->getLocale();
        if (in_array($currentLocale, self::$kurdishLocales)) {
            $kurdishTranslation = $this->translations()
                ->whereIn('locale', self::$kurdishLocales)
                ->where('key', $key)
                ->first();
            
            if ($kurdishTranslation) {
                return $kurdishTranslation->value;
            }
        }

        return $defaultValue;
    }

    /**
     * Boot the trait to load Kurdish translations
     */
    protected static function bootHasKurdishFallback()
    {
        static::addGlobalScope('kurdish_translate', function ($builder) {
            $builder->with(['translations' => function ($query) {
                $currentLocale = app()->getLocale();
                if (in_array($currentLocale, self::$kurdishLocales, true)) {
                    return $query->whereIn('locale', self::$kurdishLocales);
                }
                return $query->where('locale', $currentLocale);
            }]);
        });
    }
}